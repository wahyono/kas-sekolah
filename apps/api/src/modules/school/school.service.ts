import { Injectable, NotFoundException, ConflictException, ForbiddenException } from '@nestjs/common';
import { PrismaService } from '../../core/database/prisma.service';
import { CreateSchoolDto } from './dto/create-school.dto';
import { UpdateSchoolDto } from './dto/update-school.dto';

@Injectable()
export class SchoolService {
  constructor(private readonly prisma: PrismaService) {}

  async create(createSchoolDto: CreateSchoolDto) {
    const existingSchool = await this.prisma.school.findUnique({
      where: { code: createSchoolDto.code },
    });

    if (existingSchool) {
      throw new ConflictException(`School with code '${createSchoolDto.code}' already exists`);
    }

    return this.prisma.school.create({
      data: createSchoolDto,
    });
  }

  async findAll() {
    return this.prisma.school.findMany({
      where: {
        code: { not: 'PUSAT' }
      },
      orderBy: { createdAt: 'desc' },
    });
  }

  async findOne(id: string) {
    const school = await this.prisma.school.findUnique({
      where: { id },
    });

    if (!school) {
      throw new NotFoundException(`School with ID '${id}' not found`);
    }

    return school;
  }

  async update(id: string, updateSchoolDto: UpdateSchoolDto) {
    const school = await this.prisma.school.findUnique({ where: { id } });
    if (!school) {
      throw new NotFoundException(`School with ID '${id}' not found`);
    }

    if (school.code === 'PUSAT') {
      throw new ForbiddenException('Cannot modify the headquarters school');
    }

    if (updateSchoolDto.code && updateSchoolDto.code !== school.code) {
      const existingSchool = await this.prisma.school.findUnique({
        where: { code: updateSchoolDto.code },
      });
      if (existingSchool) {
        throw new ConflictException(`School with code '${updateSchoolDto.code}' already exists`);
      }
    }

    return this.prisma.school.update({
      where: { id },
      data: updateSchoolDto,
    });
  }

  async remove(id: string) {
    const school = await this.prisma.school.findUnique({ where: { id } });
    if (!school) {
      throw new NotFoundException(`School with ID '${id}' not found`);
    }

    if (school.code === 'PUSAT') {
      throw new ForbiddenException('Cannot delete the headquarters school as it binds SUPER_ADMINs');
    }

    return this.prisma.school.delete({
      where: { id },
    });
  }
}
