import { Injectable, UnauthorizedException, ConflictException, NotFoundException, Logger, BadRequestException } from '@nestjs/common';
import { JwtService } from '@nestjs/jwt';
import * as argon2 from 'argon2';
import { PrismaService } from '../../core/database/prisma.service';
import { LoginDto } from './dto/login.dto';
import { RegisterDto } from './dto/register.dto';

@Injectable()
export class AuthService {
  private readonly logger = new Logger(AuthService.name);

  constructor(
    private prisma: PrismaService,
    private jwtService: JwtService,
  ) {}

  async register(dto: RegisterDto) {
    let resolvedSchoolId = dto.schoolId;

    if (dto.role === 'SUPER_ADMIN') {
      if (!resolvedSchoolId) {
        let hq = await this.prisma.school.findUnique({ where: { code: 'PUSAT' } });
        if (!hq) {
          hq = await this.prisma.school.create({
            data: { name: 'Pusat Sistem Kas', code: 'PUSAT', address: 'Kantor Pusat' }
          });
        }
        resolvedSchoolId = hq.id;
      }
    } else {
      if (!resolvedSchoolId) {
        throw new BadRequestException('School ID is required for this role');
      }
    }

    const schoolExists = await this.prisma.school.findUnique({
      where: { id: resolvedSchoolId },
    });

    if (!schoolExists) {
      throw new NotFoundException(`School with ID '${resolvedSchoolId}' not found`);
    }

    const existingUser = await this.prisma.user.findUnique({
      where: { email: dto.email },
    });

    if (existingUser) {
      throw new ConflictException(`User with email '${dto.email}' already exists`);
    }

    const passwordHash = await argon2.hash(dto.password);

    const user = await this.prisma.user.create({
      data: {
        schoolId: resolvedSchoolId,
        name: dto.name,
        email: dto.email,
        phone: dto.phone,
        nis: dto.nis,
        nisn: dto.nisn,
        studentId: dto.studentId,
        gender: dto.gender,
        passwordHash,
        role: dto.role,
        managedClass: dto.role === 'KORLAS' ? dto.managedClassId : undefined,
        enrollments: (dto.role === 'STUDENT' && dto.enrolledClassId) ? {
          create: [{ classId: dto.enrolledClassId }]
        } : undefined,
      },
      select: {
        id: true,
        schoolId: true,
        name: true,
        email: true,
        role: true,
        nis: true,
        nisn: true,
        studentId: true,
        managedClass: true,
        createdAt: true,
        enrollments: { include: { class: true } }
      },
    });

    this.logger.log(`User registered successfully: ${user.email} (${user.role})`);
    return user;
  }

  async login(dto: LoginDto) {
    const user = await this.prisma.user.findUnique({
      where: { email: dto.email },
    });

    if (!user) {
      throw new UnauthorizedException('Invalid email or password credentials');
    }

    let isPasswordValid = false;
    try {
      if (user.passwordHash) {
        isPasswordValid = await argon2.verify(user.passwordHash, dto.password);
      }
    } catch (e) {
      isPasswordValid = false;
    }

    if (!isPasswordValid) {
      throw new UnauthorizedException('Invalid email or password credentials');
    }

    if (!user.isActive) {
      throw new UnauthorizedException('Account has been deactivated. Please contact your administrator');
    }

    const payload = {
      sub: user.id,
      email: user.email,
      role: user.role,
      schoolId: user.schoolId,
    };

    const accessToken = this.jwtService.sign(payload);

    this.logger.log(`User logged in successfully: ${user.email} (${user.role})`);

    return {
      accessToken,
      user: {
        id: user.id,
        name: user.name,
        email: user.email,
        role: user.role,
        schoolId: user.schoolId,
        nis: user.nis,
        nisn: user.nisn,
        studentId: user.studentId,
        managedClass: user.managedClass,
      },
    };
  }

  async getProfile(userId: string) {
    const user = await this.prisma.user.findUnique({
      where: { id: userId },
      select: {
        id: true,
        name: true,
        email: true,
        phone: true,
        role: true,
        nis: true,
        nisn: true,
        studentId: true,
        childStudent: {
          select: {
            id: true,
            name: true,
            nis: true,
            nisn: true,
          },
        },
        school: {
          select: {
            id: true,
            name: true,
            code: true,
          },
        },
        createdAt: true,
      },
    });

    if (!user) {
      throw new NotFoundException('User profile not found');
    }

    return user;
  }
}
