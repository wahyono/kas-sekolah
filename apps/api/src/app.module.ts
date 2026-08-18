import { Module } from '@nestjs/common';
import { ConfigModule } from '@nestjs/config';
import { DatabaseModule } from './core/database/database.module';
import { AuthModule } from './modules/auth/auth.module';
import { CashAccountModule } from './modules/cash-account/cash-account.module';
import { TransactionModule } from './modules/transaction/transaction.module';
import { BillingModule } from './modules/billing/billing.module';
import { StudentModule } from './modules/student/student.module';
import { NotificationModule } from './modules/notification/notification.module';
import { SchoolModule } from './modules/school/school.module';
import { UserModule } from './modules/user/user.module';
import { ClassModule } from './modules/class/class.module';
import { AcademicYearModule } from './modules/academic-year/academic-year.module';

@Module({
  imports: [
    ConfigModule.forRoot({
      isGlobal: true,
    }),
    DatabaseModule,
    AuthModule,
    CashAccountModule,
    TransactionModule,
    BillingModule,
    StudentModule,
    NotificationModule,
    SchoolModule,
    UserModule,
    ClassModule,
    AcademicYearModule,
  ],
})
export class AppModule {}
