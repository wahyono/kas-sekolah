import { Controller, Get, Post, Body, Patch, Param, Delete, UseGuards, Query } from '@nestjs/common';
import { ApiTags, ApiOperation, ApiBearerAuth, ApiQuery } from '@nestjs/swagger';
import { ClassService } from './class.service';
import { CreateClassDto } from './dto/create-class.dto';
import { UpdateClassDto } from './dto/update-class.dto';
import { JwtAuthGuard } from '../../core/guards/jwt-auth.guard';
import { RolesGuard } from '../../core/guards/roles.guard';
import { Roles } from '../../core/decorators/roles.decorator';
import { UserRole } from '@prisma/client';

@ApiTags('Classes')
@ApiBearerAuth()
@UseGuards(JwtAuthGuard, RolesGuard)
@Controller('classes')
export class ClassController {
  constructor(private readonly classService: ClassService) {}

  @Post()
  @Roles(UserRole.SUPER_ADMIN, UserRole.ADMIN)
  @ApiOperation({ summary: 'Create a new class' })
  async create(@Body() createClassDto: CreateClassDto) {
    return this.classService.create(createClassDto);
  }

  @Get()
  @ApiOperation({ summary: 'Get all classes' })
  @ApiQuery({ name: 'academicYearId', required: false })
  @ApiQuery({ name: 'schoolId', required: false })
  async findAll(
    @Query('academicYearId') academicYearId?: string,
    @Query('schoolId') schoolId?: string
  ) {
    return this.classService.findAll(academicYearId, schoolId);
  }

  @Get(':id')
  @ApiOperation({ summary: 'Get a specific class by id' })
  async findOne(@Param('id') id: string) {
    return this.classService.findOne(id);
  }

  @Patch(':id')
  @Roles(UserRole.SUPER_ADMIN, UserRole.ADMIN)
  @ApiOperation({ summary: 'Update a class' })
  async update(@Param('id') id: string, @Body() updateClassDto: UpdateClassDto) {
    return this.classService.update(id, updateClassDto);
  }

  @Delete(':id')
  @Roles(UserRole.SUPER_ADMIN, UserRole.ADMIN)
  @ApiOperation({ summary: 'Delete a class' })
  async remove(@Param('id') id: string) {
    return this.classService.remove(id);
  }
}
