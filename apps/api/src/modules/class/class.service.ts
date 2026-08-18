import { Injectable, NotFoundException } from '@nestjs/common';
import { PrismaService } from '../../core/database/prisma.service';
import { CreateClassDto } from './dto/create-class.dto';
import { UpdateClassDto } from './dto/update-class.dto';

@Injectable()
export class ClassService {
  constructor(private prisma: PrismaService) {}

  async create(dto: CreateClassDto) {
    return this.prisma.$transaction(async (tx) => {
      const newClass = await tx.class.create({
        data: dto,
      });

      await tx.cashAccount.create({
        data: {
          classId: newClass.id,
          name: 'Kas Utama',
          currency: 'IDR',
        },
      });

      return newClass;
    });
  }

  async findAll(academicYearId?: string, schoolId?: string) {
    const where: any = {};
    if (academicYearId) where.academicYearId = academicYearId;
    if (schoolId) where.academicYear = { schoolId };

    return this.prisma.class.findMany({
      where,
      include: {
        academicYear: true,
      },
      orderBy: { name: 'asc' },
    });
  }

  async findOne(id: string) {
    const classData = await this.prisma.class.findUnique({
      where: { id },
      include: {
        academicYear: true,
        enrollments: {
          include: {
            student: {
              select: { id: true, name: true, nis: true, email: true },
            },
          },
        },
      },
    });

    if (!classData) {
      throw new NotFoundException(`Class with ID ${id} not found`);
    }
    return classData;
  }

  async update(id: string, dto: UpdateClassDto) {
    const existing = await this.prisma.class.findUnique({ where: { id } });
    if (!existing) {
      throw new NotFoundException(`Class with ID ${id} not found`);
    }

    return this.prisma.class.update({
      where: { id },
      data: dto,
    });
  }

  async remove(id: string) {
    const existing = await this.prisma.class.findUnique({ where: { id } });
    if (!existing) {
      throw new NotFoundException(`Class with ID ${id} not found`);
    }

    return this.prisma.class.delete({
      where: { id },
    });
  }
}
