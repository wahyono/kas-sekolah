import { Injectable, Logger } from '@nestjs/common';
import { Cron, CronExpression } from '@nestjs/schedule';
import { PrismaService } from '../../core/database/prisma.service';
import { NotificationService } from './notification.service';
import { BillingStatus } from '@prisma/client';

@Injectable()
export class CronDuesService {
  private readonly logger = new Logger(CronDuesService.name);

  constructor(
    private prisma: PrismaService,
    private notificationService: NotificationService,
  ) {}

  // Automatically runs at midnight on the 1st day of every month
  @Cron(CronExpression.EVERY_1ST_DAY_OF_MONTH_AT_MIDNIGHT)
  async handleMonthlyDuesCron() {
    this.logger.log('⏰ Executing Automated Monthly Dues Scheduler for SD Negeri 08 PAGI...');

    const cashAccounts = await this.prisma.cashAccount.findMany({
      include: {
        class: {
          include: {
            enrollments: {
              include: {
                student: true,
              },
            },
          },
        },
      },
    });

    for (const account of cashAccounts) {
      const monthYear = new Date().toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
      const schemeTitle = `Iuran Kas Wajib Bulanan ${monthYear}`;
      const amount = 25000;
      const dueDate = new Date();
      dueDate.setDate(dueDate.getDate() + 25); // Due in 25 days

      const students = account.class.enrollments;
      if (students.length === 0) continue;

      await this.prisma.$transaction(async (tx) => {
        const scheme = await tx.duesScheme.create({
          data: {
            cashAccountId: account.id,
            title: schemeTitle,
            amount: amount,
            dueDate: dueDate,
          },
        });

        const billingData = students.map((e) => ({
          duesSchemeId: scheme.id,
          studentId: e.studentId,
          amountDue: amount,
          amountPaid: 0,
          status: BillingStatus.PENDING,
          dueDate: dueDate,
        }));

        await tx.studentBilling.createMany({ data: billingData });

        this.logger.log(`Created automated cron scheme '${schemeTitle}' for ${billingData.length} students.`);

        // Dispatch Email Notifications for each student
        for (const enrollment of students) {
          await this.notificationService.sendBillingNotificationEmail(
            enrollment.student.email,
            enrollment.student.name,
            schemeTitle,
            amount,
            dueDate,
          );
        }
      });
    }
  }
}
