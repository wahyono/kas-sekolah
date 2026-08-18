import { Controller, Get, Post, Body, Patch, Param, Delete, UseGuards, Query } from '@nestjs/common';
import { ApiTags, ApiOperation, ApiBearerAuth, ApiQuery, ApiProperty, PartialType } from '@nestjs/swagger';
import { Injectable, NotFoundException, Module } from '@nestjs/common';
import { PrismaService } from '../../core/database/prisma.service';
import { IsNotEmpty, IsString, IsBoolean, IsOptional, IsUUID } from 'class-validator';
import { JwtAuthGuard } from '../../core/guards/jwt-auth.guard';
import { RolesGuard } from '../../core/guards/roles.guard';
import { Roles } from '../../core/decorators/roles.decorator';
import { UserRole } from '@prisma/client';

export class CreateAcademicYearDto {
  @ApiProperty({ example: 'uuid' })
  @IsUUID('4')
  @IsNotEmpty()
  schoolId!: string;

  @ApiProperty({ example: '2026/2027' })
  @IsString()
  @IsNotEmpty()
  year!: string;

  @ApiProperty({ example: true })
  @IsOptional()
  @IsBoolean()
  isCurrent?: boolean;
}

export class UpdateAcademicYearDto extends PartialType(CreateAcademicYearDto) {}

@Injectable()
export class AcademicYearService {
  constructor(private prisma: PrismaService) {}

  async create(dto: CreateAcademicYearDto) {
    if (dto.isCurrent) {
      await this.prisma.academicYear.updateMany({
        where: { schoolId: dto.schoolId },
        data: { isCurrent: false }
      });
    }
    return this.prisma.academicYear.create({ data: dto });
  }

  async findAll(schoolId?: string) {
    const where = schoolId ? { schoolId } : {};
    return this.prisma.academicYear.findMany({ where, orderBy: { year: 'desc' } });
  }

  async update(id: string, dto: UpdateAcademicYearDto) {
    const existing = await this.prisma.academicYear.findUnique({ where: { id } });
    if (!existing) throw new NotFoundException('Academic year not found');
    
    if (dto.isCurrent) {
      await this.prisma.academicYear.updateMany({
        where: { schoolId: dto.schoolId || existing.schoolId },
        data: { isCurrent: false }
      });
    }
    return this.prisma.academicYear.update({ where: { id }, data: dto });
  }

  async remove(id: string) {
    return this.prisma.academicYear.delete({ where: { id } });
  }
}

@ApiTags('Academic Years')
@ApiBearerAuth()
@UseGuards(JwtAuthGuard, RolesGuard)
@Controller('academic-years')
export class AcademicYearController {
  constructor(private readonly service: AcademicYearService) {}

  @Post()
  @Roles(UserRole.SUPER_ADMIN, UserRole.ADMIN)
  async create(@Body() dto: CreateAcademicYearDto) {
    return this.service.create(dto);
  }

  @Get()
  @ApiQuery({ name: 'schoolId', required: false })
  async findAll(@Query('schoolId') schoolId?: string) {
    return this.service.findAll(schoolId);
  }

  @Patch(':id')
  @Roles(UserRole.SUPER_ADMIN, UserRole.ADMIN)
  async update(@Param('id') id: string, @Body() dto: UpdateAcademicYearDto) {
    return this.service.update(id, dto);
  }

  @Delete(':id')
  @Roles(UserRole.SUPER_ADMIN, UserRole.ADMIN)
  async remove(@Param('id') id: string) {
    return this.service.remove(id);
  }
}

@Module({
  controllers: [AcademicYearController],
  providers: [AcademicYearService, PrismaService],
  exports: [AcademicYearService],
})
export class AcademicYearModule {}
