import { Module } from '@nestjs/common';
import { ScheduleModule } from '@nestjs/schedule';
import { NotificationService } from './notification.service';
import { CronDuesService } from './cron.service';
import { NotificationController } from './notification.controller';

@Module({
  imports: [ScheduleModule.forRoot()],
  controllers: [NotificationController],
  providers: [NotificationService, CronDuesService],
  exports: [NotificationService, CronDuesService],
})
export class NotificationModule {}
