import { Injectable, NotFoundException } from '@nestjs/common';
import { PrismaService } from '../../core/database/prisma.service';
import { UpdateUserDto } from './dto/update-user.dto';
import * as argon2 from 'argon2';

@Injectable()
export class UserService {
  constructor(private prisma: PrismaService) {}

  async findAll(userRole?: string, schoolId?: string, search?: string, userId?: string) {
    const andConditions: any[] = [];

    if (userRole === 'KORLAS' && userId) {
      const korlasUser = await this.prisma.user.findUnique({ where: { id: userId }, select: { managedClass: true, schoolId: true } });
      if (korlasUser) {
        andConditions.push({ schoolId: korlasUser.schoolId });
        if (korlasUser.managedClass) {
          andConditions.push({
            enrollments: {
              some: {
                classId: korlasUser.managedClass
              }
            }
          });
        } else {
          // If KORLAS somehow has no managedClass, return nothing
          andConditions.push({ id: 'none' });
        }
      }
    } else if (schoolId) {
      if (userRole === 'SUPER_ADMIN') {
        andConditions.push({
          OR: [
            { schoolId: schoolId },
            { role: 'SUPER_ADMIN' }
          ]
        });
      } else {
        andConditions.push({ schoolId: schoolId });
      }
    }

    if (search) {
      andConditions.push({
        OR: [
          { name: { contains: search, mode: 'insensitive' } },
          { email: { contains: search, mode: 'insensitive' } },
        ]
      });
    }

    const where = andConditions.length > 0 ? { AND: andConditions } : {};

    return this.prisma.user.findMany({
      where,
      select: {
        id: true,
        schoolId: true,
        name: true,
        email: true,
        phone: true,
        role: true,
        isActive: true,
        createdAt: true,
        school: {
          select: {
            id: true,
            name: true,
          }
        },
        managedClass: true,
        enrollments: {
          include: {
            class: true
          }
        },
      },
      orderBy: { createdAt: 'desc' },
    });
  }

  async findOne(id: string) {
    const user = await this.prisma.user.findUnique({
      where: { id },
      select: {
        id: true,
        schoolId: true,
        name: true,
        email: true,
        phone: true,
        role: true,
        isActive: true,
        createdAt: true,
      },
    });

    if (!user) {
      throw new NotFoundException(`User with ID ${id} not found`);
    }
    return user;
  }

  async update(id: string, dto: UpdateUserDto) {
    const user = await this.prisma.user.findUnique({ where: { id } });
    if (!user) {
      throw new NotFoundException(`User with ID ${id} not found`);
    }

    const data: any = { ...dto };
    if (dto.password) {
      data.passwordHash = await argon2.hash(dto.password);
      delete data.password;
    }

    return this.prisma.user.update({
      where: { id },
      data,
      select: {
        id: true,
        name: true,
        email: true,
        role: true,
        isActive: true,
      },
    });
  }

  async remove(id: string) {
    const user = await this.prisma.user.findUnique({ where: { id } });
    if (!user) {
      throw new NotFoundException(`User with ID ${id} not found`);
    }

    return this.prisma.user.delete({
      where: { id },
    });
  }
}
