import { Controller, Post, Get, Param, Body, UseGuards, Req } from '@nestjs/common';
import { ApiTags, ApiOperation, ApiBearerAuth } from '@nestjs/swagger';
import { TransactionService } from './transaction.service';
import { RecordTransactionDto } from './dto/record-payment.dto';
import { JwtAuthGuard } from '../../core/guards/jwt-auth.guard';
import { RolesGuard } from '../../core/guards/roles.guard';
import { Roles } from '../../core/decorators/roles.decorator';
import { CurrentUser } from '../../core/decorators/current-user.decorator';
import { UserRole } from '@prisma/client';

@ApiTags('Transactions & Ledger')
@ApiBearerAuth()
@UseGuards(JwtAuthGuard, RolesGuard)
@Controller('transactions')
export class TransactionController {
  constructor(private readonly service: TransactionService) {}

  @Post()
  @Roles(UserRole.SUPER_ADMIN, UserRole.ADMIN, UserRole.TREASURER, UserRole.KORLAS)
  @ApiOperation({ summary: 'Record a cash transaction (Income or Expense) atomically' })
  async recordTransaction(
    @Body() dto: RecordTransactionDto,
    @Req() req: any,
  ) {
    return this.service.recordTransaction(dto, req.user.id, req.user.role);
  }

  @Get('account/:cashAccountId')
  @ApiOperation({ summary: 'Get transaction history for a specific cash account' })
  async getByAccount(@Param('cashAccountId') cashAccountId: string) {
    return this.service.getTransactionsByAccount(cashAccountId);
  }

  @Get('school/:schoolId')
  @ApiOperation({ summary: 'Get transaction history for an entire school' })
  async getBySchool(@Param('schoolId') schoolId: string, @Req() req: any) {
    return this.service.getTransactionsBySchool(schoolId, req.user.role, req.user.id);
  }
}
