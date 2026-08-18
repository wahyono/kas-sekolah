import { Injectable, NotFoundException, ForbiddenException, Logger } from '@nestjs/common';
import { PrismaService } from '../../core/database/prisma.service';
import { CreateDuesSchemeDto } from './dto/create-dues-scheme.dto';
import { BillingStatus } from '@prisma/client';

@Injectable()
export class BillingService {
  private readonly logger = new Logger(BillingService.name);

  constructor(private prisma: PrismaService) {}

  async createDuesSchemeAndGenerateBillings(dto: CreateDuesSchemeDto, userRole?: string, userId?: string) {
    const cashAccount = await this.prisma.cashAccount.findUnique({
      where: { id: dto.cashAccountId },
      include: {
        class: {
          include: {
            enrollments: true,
          },
        },
      },
    });

    if (!cashAccount) {
      throw new NotFoundException(`Cash Account '${dto.cashAccountId}' not found`);
    }

    if (userRole === 'KORLAS' && userId) {
      const korlasUser = await this.prisma.user.findUnique({ where: { id: userId }, select: { managedClass: true } });
      if (!korlasUser || korlasUser.managedClass !== cashAccount.classId) {
        throw new ForbiddenException('KORLAS is not authorized to create dues scheme for this class');
      }
    }

    const students = cashAccount.class.enrollments;
    if (students.length === 0) {
      throw new NotFoundException(`No students currently enrolled in class '${cashAccount.class.name}'`);
    }

    return this.prisma.$transaction(async (tx) => {
      // 1. Create the Dues Scheme
      const scheme = await tx.duesScheme.create({
        data: {
          cashAccountId: dto.cashAccountId,
          title: dto.title,
          amount: dto.amount,
          dueDate: new Date(dto.dueDate),
        },
      });

      // 2. Batch generate billing records for enrolled students
      const billingData = students.map((enrollment) => ({
        duesSchemeId: scheme.id,
        studentId: enrollment.studentId,
        amountDue: dto.amount,
        amountPaid: 0,
        status: BillingStatus.PENDING,
        dueDate: new Date(dto.dueDate),
      }));

      await tx.studentBilling.createMany({
        data: billingData,
      });

      this.logger.log(
        `Created dues scheme '${scheme.title}' and generated ${billingData.length} student billing records`,
      );

      return {
        scheme,
        totalBillingsGenerated: billingData.length,
      };
    });
  }

  async getStudentBillings(studentId: string) {
    return this.prisma.studentBilling.findMany({
      where: { studentId },
      include: {
        duesScheme: {
          include: {
            cashAccount: { select: { name: true, currency: true } },
          },
        },
      },
      orderBy: { dueDate: 'asc' },
    });
  }

  async getBillingsByScheme(schemeId: string) {
    return this.prisma.studentBilling.findMany({
      where: { duesSchemeId: schemeId },
      include: {
        student: { select: { id: true, name: true, email: true, phone: true } },
      },
      orderBy: { student: { name: 'asc' } },
    });
  }

  async getBillingsByClass(classId: string) {
    return this.prisma.studentBilling.findMany({
      where: {
        student: {
          enrollments: {
            some: { classId },
          },
        },
      },
      include: {
        student: { select: { id: true, name: true } },
        duesScheme: { select: { title: true, amount: true, dueDate: true, cashAccount: { select: { classId: true } } } },
      },
      orderBy: { dueDate: 'desc' },
    });
  }

  async getBillingsBySchool(schoolId: string, userRole?: string, userId?: string) {
    let whereCondition: any = { student: { schoolId } };

    if (userRole === 'KORLAS' && userId) {
      const korlasUser = await this.prisma.user.findUnique({ where: { id: userId }, select: { managedClass: true } });
      if (korlasUser?.managedClass) {
        whereCondition.student.enrollments = {
          some: { classId: korlasUser.managedClass }
        };
      } else {
        whereCondition.id = 'none'; // Ensure no results if managedClass is missing
      }
    }

    return this.prisma.studentBilling.findMany({
      where: whereCondition,
      include: {
        student: { select: { id: true, name: true, enrollments: { include: { class: true } } } },
        duesScheme: { select: { title: true, amount: true, dueDate: true } },
      },
      orderBy: { dueDate: 'desc' },
    });
  }
}
