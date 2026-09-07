import { Injectable, NotFoundException, ConflictException, Logger } from '@nestjs/common';
import { PrismaService } from '../../core/database/prisma.service';
import { CreateStudentDto } from './dto/create-student.dto';
import { UpdateStudentDto } from './dto/update-student.dto';
import { UserRole } from '@prisma/client';
import * as argon2 from 'argon2';

@Injectable()
export class StudentService {
  private readonly logger = new Logger(StudentService.name);

  constructor(private prisma: PrismaService) {}

  async create(dto: CreateStudentDto) {
    // 1. Verify Unique NIS & NISN & Email
    const existingNis = await this.prisma.user.findFirst({
      where: { nis: dto.nis },
    });
    if (existingNis) {
      throw new ConflictException(`Student with NIS '${dto.nis}' already exists`);
    }

    const existingNisn = await this.prisma.user.findFirst({
      where: { nisn: dto.nisn },
    });
    if (existingNisn) {
      throw new ConflictException(`Student with NISN '${dto.nisn}' already exists`);
    }

    const existingEmail = await this.prisma.user.findUnique({
      where: { email: dto.email },
    });
    if (existingEmail) {
      throw new ConflictException(`User with email '${dto.email}' already exists`);
    }

    const passwordHash = await argon2.hash(dto.password);

    const student = await this.prisma.user.create({
      data: {
        schoolId: dto.schoolId,
        nis: dto.nis,
        nisn: dto.nisn,
        name: dto.name,
        email: dto.email,
        phone: dto.phone,
        gender: dto.gender,
        religion: dto.religion,
        passwordHash,
        role: UserRole.STUDENT,
      },
      select: {
        id: true,
        schoolId: true,
        nis: true,
        nisn: true,
        name: true,
        email: true,
        phone: true,
        gender: true,
        religion: true,
        role: true,
        isActive: true,
        createdAt: true,
      },
    });

    this.logger.log(`Created student profile: ${student.name} (NIS: ${student.nis}, NISN: ${student.nisn})`);
    return student;
  }

  async findAll(schoolId?: string, search?: string) {
    return this.prisma.user.findMany({
      where: {
        role: UserRole.STUDENT,
        ...(schoolId ? { schoolId } : {}),
        ...(search
          ? {
              OR: [
                { name: { contains: search } },
                { nis: { contains: search } },
                { nisn: { contains: search } },
                { email: { contains: search } },
              ],
            }
          : {}),
      },
      select: {
        id: true,
        nis: true,
        nisn: true,
        name: true,
        email: true,
        phone: true,
        gender: true,
        religion: true,
        isActive: true,
        enrollments: {
          include: {
            class: { select: { id: true, name: true } },
          },
        },
        createdAt: true,
      },
      orderBy: { name: 'asc' },
    });
  }

  async findOne(id: string) {
    const student = await this.prisma.user.findFirst({
      where: { id, role: UserRole.STUDENT },
      select: {
        id: true,
        schoolId: true,
        nis: true,
        nisn: true,
        name: true,
        email: true,
        phone: true,
        gender: true,
        religion: true,
        isActive: true,
        enrollments: {
          include: { class: true },
        },
        billings: {
          include: { duesScheme: true },
        },
        createdAt: true,
      },
    });

    if (!student) {
      throw new NotFoundException(`Student record with ID '${id}' not found`);
    }

    return student;
  }

  async update(id: string, dto: UpdateStudentDto) {
    await this.findOne(id); // Ensure exists

    const updateData: any = { ...dto };
    if (dto.password) {
      updateData.passwordHash = await argon2.hash(dto.password);
      delete updateData.password;
    }

    const updated = await this.prisma.user.update({
      where: { id },
      data: updateData,
      select: {
        id: true,
        nis: true,
        nisn: true,
        name: true,
        email: true,
        gender: true,
        religion: true,
        updatedAt: true,
      },
    });

    this.logger.log(`Updated student record: ${updated.name} (ID: ${updated.id})`);
    return updated;
  }

  async remove(id: string) {
    await this.findOne(id);

    // Deactivate user instead of hard delete to preserve financial ledger history
    const deactivated = await this.prisma.user.update({
      where: { id },
      data: { isActive: false },
    });

    this.logger.log(`Deactivated student record: ${deactivated.name} (ID: ${deactivated.id})`);
    return { message: 'Student account deactivated successfully', id };
  }
}
