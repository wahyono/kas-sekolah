import { Injectable, NotFoundException, BadRequestException, ForbiddenException, Logger } from '@nestjs/common';
import { PrismaService } from '../../core/database/prisma.service';
import { RecordTransactionDto } from './dto/record-payment.dto';
import { TransactionType, BillingStatus } from '@prisma/client';

@Injectable()
export class TransactionService {
  private readonly logger = new Logger(TransactionService.name);

  constructor(private prisma: PrismaService) {}

  async recordTransaction(dto: RecordTransactionDto, userId: string, userRole?: string) {
    return this.prisma.$transaction(async (tx) => {
      const account = await tx.cashAccount.findUnique({
        where: { id: dto.cashAccountId },
      });

      if (!account) {
        throw new NotFoundException(`Cash Account '${dto.cashAccountId}' not found`);
      }

      if (userRole === 'KORLAS') {
        const korlasUser = await this.prisma.user.findUnique({ where: { id: userId }, select: { managedClass: true } });
        if (!korlasUser || korlasUser.managedClass !== account.classId) {
          throw new ForbiddenException('KORLAS is not authorized to create transactions for this class');
        }
      }

      // If EXPENSE, verify sufficient account balance
      if (dto.type === TransactionType.EXPENSE) {
        const currentBal = Number(account.currentBalance);
        if (currentBal < dto.amount) {
          throw new BadRequestException(
            `Insufficient cash balance. Account balance is ${currentBal}, requested expense is ${dto.amount}`,
          );
        }
      }

      // Calculate new balance
      const balanceAdjustment = dto.type === TransactionType.INCOME ? dto.amount : -dto.amount;
      const updatedAccount = await tx.cashAccount.update({
        where: { id: dto.cashAccountId },
        data: {
          currentBalance: {
            increment: balanceAdjustment,
          },
        },
      });

      // Update student billing status if billingId provided
      if (dto.billingId) {
        const billing = await tx.studentBilling.findUnique({
          where: { id: dto.billingId },
        });

        if (billing) {
          const newAmountPaid = Number(billing.amountPaid) + dto.amount;
          const amountDue = Number(billing.amountDue);
          const newStatus =
            newAmountPaid >= amountDue
              ? BillingStatus.PAID
              : newAmountPaid > 0
              ? BillingStatus.PARTIAL
              : BillingStatus.PENDING;

          await tx.studentBilling.update({
            where: { id: dto.billingId },
            data: {
              amountPaid: newAmountPaid,
              status: newStatus,
            },
          });
        }
      }

      // Record immutable ledger entry
      const transaction = await tx.transaction.create({
        data: {
          cashAccountId: dto.cashAccountId,
          billingId: dto.billingId,
          type: dto.type,
          amount: dto.amount,
          category: dto.category,
          description: dto.description,
          receiptUrl: dto.receiptUrl,
          createdBy: userId,
        },
      });

      this.logger.log(
        `Recorded ${dto.type} of IDR ${dto.amount} for Account ${account.name}. New Balance: ${updatedAccount.currentBalance}`,
      );

      return {
        transaction,
        newBalance: updatedAccount.currentBalance,
      };
    });
  }

  async getTransactionsByAccount(cashAccountId: string) {
    return this.prisma.transaction.findMany({
      where: { cashAccountId },
      include: {
        creator: {
          select: { id: true, name: true, role: true },
        },
        billing: {
          include: {
            student: { select: { name: true, email: true } },
          },
        },
      },
      orderBy: { createdAt: 'desc' },
    });
  }

  async getTransactionsBySchool(schoolId: string, userRole?: string, userId?: string) {
    let whereCondition: any = {
      cashAccount: {
        class: {
          academicYear: { schoolId }
        }
      }
    };

    if (userRole === 'KORLAS' && userId) {
      const korlasUser = await this.prisma.user.findUnique({ where: { id: userId }, select: { managedClass: true } });
      if (korlasUser?.managedClass) {
        whereCondition.cashAccount.classId = korlasUser.managedClass;
      } else {
        whereCondition.id = 'none';
      }
    }

    return this.prisma.transaction.findMany({
      where: whereCondition,
      include: {
        creator: {
          select: { id: true, name: true, role: true },
        },
        billing: {
          include: {
            student: { select: { name: true, email: true } },
          },
        },
        cashAccount: {
          include: {
            class: { select: { id: true, name: true } }
          }
        }
      },
      orderBy: { createdAt: 'desc' },
    });
  }
}
