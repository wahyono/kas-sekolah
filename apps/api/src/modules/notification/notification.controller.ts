import { Controller, Post, Body, UseGuards } from '@nestjs/common';
import { ApiTags, ApiOperation, ApiBearerAuth } from '@nestjs/swagger';
import { NotificationService } from './notification.service';
import { CronDuesService } from './cron.service';
import { JwtAuthGuard } from '../../core/guards/jwt-auth.guard';
import { RolesGuard } from '../../core/guards/roles.guard';
import { Roles } from '../../core/decorators/roles.decorator';
import { UserRole } from '@prisma/client';

@ApiTags('Notification & Reports Emailing')
@ApiBearerAuth()
@UseGuards(JwtAuthGuard, RolesGuard)
@Controller('notifications')
export class NotificationController {
  constructor(
    private readonly notificationService: NotificationService,
    private readonly cronDuesService: CronDuesService,
  ) {}

  @Post('email-report')
  @Roles(UserRole.SUPER_ADMIN, UserRole.ADMIN, UserRole.TREASURER, UserRole.KORLAS)
  @ApiOperation({ summary: 'Email PDF Financial Report Archive to specified email' })
  async emailReport(
    @Body('email') email: string,
    @Body('reportTitle') reportTitle: string,
    @Body('pdf') pdf?: string,
  ) {
    return this.notificationService.sendReportArchiveEmail(
      email || 'admin@sdn08pagi.sch.id',
      reportTitle || 'Laporan Kas Kelas',
      pdf || 'PDF Buffer Encoded',
    );
  }

  @Post('trigger-cron-dues')
  @Roles(UserRole.SUPER_ADMIN, UserRole.ADMIN)
  @ApiOperation({ summary: 'Manually trigger scheduled monthly dues cron generator and dispatch emails' })
  async triggerCron() {
    await this.cronDuesService.handleMonthlyDuesCron();
    return { message: 'Automated dues cron job executed successfully. Notifications dispatched.' };
  }
}
