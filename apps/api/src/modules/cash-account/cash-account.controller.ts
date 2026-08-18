import { Controller, Post, Get, Param, Body, UseGuards } from '@nestjs/common';
import { ApiTags, ApiOperation, ApiBearerAuth } from '@nestjs/swagger';
import { CashAccountService } from './cash-account.service';
import { CreateCashAccountDto } from './dto/create-cash-account.dto';
import { JwtAuthGuard } from '../../core/guards/jwt-auth.guard';
import { RolesGuard } from '../../core/guards/roles.guard';
import { Roles } from '../../core/decorators/roles.decorator';
import { UserRole } from '@prisma/client';

@ApiTags('Cash Accounts')
@ApiBearerAuth()
@UseGuards(JwtAuthGuard, RolesGuard)
@Controller('cash-accounts')
export class CashAccountController {
  constructor(private readonly service: CashAccountService) {}

  @Post()
  @Roles(UserRole.SUPER_ADMIN, UserRole.ADMIN, UserRole.TREASURER)
  @ApiOperation({ summary: 'Create a new class cash account' })
  async create(@Body() dto: CreateCashAccountDto) {
    return this.service.create(dto);
  }

  @Get('class/:classId')
  @ApiOperation({ summary: 'Get cash accounts by class ID' })
  async findByClass(@Param('classId') classId: string) {
    return this.service.findByClass(classId);
  }

  @Get(':id/summary')
  @ApiOperation({ summary: 'Get detailed summary & recent ledger transactions for account' })
  async getSummary(@Param('id') id: string) {
    return this.service.getAccountSummary(id);
  }
}
