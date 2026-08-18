import { Injectable, Logger, OnApplicationBootstrap } from '@nestjs/common';
import { PrismaService } from './prisma.service';
import * as argon2 from 'argon2';
import { UserRole, Gender, Religion } from '@prisma/client';

@Injectable()
export class SeedService implements OnApplicationBootstrap {
  private readonly logger = new Logger(SeedService.name);

  constructor(private prisma: PrismaService) {}

  async onApplicationBootstrap() {
    await this.seedDemoData();
  }

  async seedDemoData() {
    const schoolCount = await this.prisma.school.count();
    if (schoolCount > 0) {
      this.logger.log('Database already seeded. Skipping initial bootstrap seeding.');
      return;
    }

    this.logger.log('Seeding initial enterprise demo data for SD Negeri 08 PAGI...');

    // 1. Create School (SD Negeri 08 PAGI)
    const school = await this.prisma.school.create({
      data: {
        name: 'SD Negeri 08 PAGI',
        code: 'SDN08PAGI',
        address: 'Jl. Utama No. 8, Jakarta',
        phone: '021-5550808',
      },
    });

    // 2. Passwords
    const defaultPasswordHash = await argon2.hash('Password123!');

    // 3. Admin & Treasurer Users
    await this.prisma.user.create({
      data: {
        schoolId: school.id,
        name: 'Budi Santoso (Admin)',
        email: 'admin@sdn08pagi.sch.id',
        passwordHash: defaultPasswordHash,
        role: UserRole.ADMIN,
      },
    });

    await this.prisma.user.create({
      data: {
        schoolId: school.id,
        name: 'Siti Rahma (Bendahara Kelas)',
        email: 'bendahara@sdn08pagi.sch.id',
        passwordHash: defaultPasswordHash,
        role: UserRole.TREASURER,
      },
    });

    // 4. Academic Year & Class
    const academicYear = await this.prisma.academicYear.create({
      data: {
        schoolId: school.id,
        year: '2026/2027',
        isCurrent: true,
      },
    });

    const classRecord = await this.prisma.class.create({
      data: {
        academicYearId: academicYear.id,
        name: 'Kelas 5-A',
      },
    });

    // 5. Cash Account
    await this.prisma.cashAccount.create({
      data: {
        classId: classRecord.id,
        name: 'Kas Utama Kelas 5-A',
        currentBalance: 500000.0,
      },
    });

    // 6. Student Users with Mandated Fields
    const studentsData = [
      {
        nis: '20260501',
        nisn: '0051234567',
        name: 'Andi Wijaya',
        email: 'andi@sdn08pagi.sch.id',
        gender: Gender.MALE,
        religion: Religion.ISLAM,
      },
      {
        nis: '20260502',
        nisn: '0051234568',
        name: 'Dewi Lestari',
        email: 'dewi@sdn08pagi.sch.id',
        gender: Gender.FEMALE,
        religion: Religion.CHRISTIAN,
      },
      {
        nis: '20260503',
        nisn: '0051234569',
        name: 'Rian Pratama',
        email: 'rian@sdn08pagi.sch.id',
        gender: Gender.MALE,
        religion: Religion.HINDU,
      },
    ];

    for (const s of studentsData) {
      const student = await this.prisma.user.create({
        data: {
          schoolId: school.id,
          nis: s.nis,
          nisn: s.nisn,
          name: s.name,
          email: s.email,
          gender: s.gender,
          religion: s.religion,
          passwordHash: defaultPasswordHash,
          role: UserRole.STUDENT,
        },
      });

      await this.prisma.classEnrollment.create({
        data: {
          classId: classRecord.id,
          studentId: student.id,
        },
      });
    }

    this.logger.log('SD Negeri 08 PAGI demo data seeded successfully!');
  }
}
