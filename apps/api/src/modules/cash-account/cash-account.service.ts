import { Injectable, NotFoundException, Logger } from '@nestjs/common';
import { PrismaService } from '../../core/database/prisma.service';
import { CreateCashAccountDto } from './dto/create-cash-account.dto';

@Injectable()
export class CashAccountService {
  private readonly logger = new Logger(CashAccountService.name);

  constructor(private prisma: PrismaService) {}

  async create(dto: CreateCashAccountDto) {
    const classRecord = await this.prisma.class.findUnique({
      where: { id: dto.classId },
    });

    if (!classRecord) {
      throw new NotFoundException(`Class with ID '${dto.classId}' not found`);
    }

    const account = await this.prisma.cashAccount.create({
      data: {
        classId: dto.classId,
        name: dto.name,
        currentBalance: dto.initialBalance || 0,
      },
    });

    this.logger.log(`Created cash account '${account.name}' for class '${dto.classId}'`);
    return account;
  }

  async findByClass(classId: string) {
    return this.prisma.cashAccount.findMany({
      where: { classId },
      include: {
        class: {
          select: { name: true },
        },
      },
    });
  }

  async getAccountSummary(id: string) {
    const account = await this.prisma.cashAccount.findUnique({
      where: { id },
      include: {
        class: true,
        transactions: {
          take: 10,
          orderBy: { createdAt: 'desc' },
          include: { creator: { select: { name: true, role: true } } },
        },
      },
    });

    if (!account) {
      throw new NotFoundException(`Cash account with ID '${id}' not found`);
    }

    return account;
  }
}
