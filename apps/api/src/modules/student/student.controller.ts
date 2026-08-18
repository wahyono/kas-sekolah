import {
  Controller,
  Get,
  Post,
  Put,
  Delete,
  Body,
  Param,
  Query,
  UseGuards,
  HttpCode,
  HttpStatus,
} from '@nestjs/common';
import { ApiTags, ApiOperation, ApiResponse, ApiBearerAuth, ApiQuery } from '@nestjs/swagger';
import { StudentService } from './student.service';
import { CreateStudentDto } from './dto/create-student.dto';
import { UpdateStudentDto } from './dto/update-student.dto';
import { JwtAuthGuard } from '../../core/guards/jwt-auth.guard';
import { RolesGuard } from '../../core/guards/roles.guard';
import { Roles } from '../../core/decorators/roles.decorator';
import { UserRole } from '@prisma/client';

@ApiTags('Student Management')
@ApiBearerAuth()
@UseGuards(JwtAuthGuard, RolesGuard)
@Controller('students')
export class StudentController {
  constructor(private readonly studentService: StudentService) {}

  @Post()
  @Roles(UserRole.SUPER_ADMIN, UserRole.ADMIN)
  @ApiOperation({ summary: 'Create a new Student record (NIS, NISN, Name, Gender, Religion mandatory)' })
  @ApiResponse({ status: 201, description: 'Student record created' })
  @ApiResponse({ status: 401, description: 'Unauthorized - Token required' })
  @ApiResponse({ status: 403, description: 'Forbidden - Admin privileges required' })
  async create(@Body() dto: CreateStudentDto) {
    return this.studentService.create(dto);
  }

  @Get()
  @ApiOperation({ summary: 'List all students with optional search filter by NIS, NISN, or Name' })
  @ApiQuery({ name: 'schoolId', required: false })
  @ApiQuery({ name: 'search', required: false })
  async findAll(
    @Query('schoolId') schoolId?: string,
    @Query('search') search?: string,
  ) {
    return this.studentService.findAll(schoolId, search);
  }

  @Get(':id')
  @ApiOperation({ summary: 'Get detailed Student profile by UUID' })
  async findOne(@Param('id') id: string) {
    return this.studentService.findOne(id);
  }

  @Put(':id')
  @Roles(UserRole.SUPER_ADMIN, UserRole.ADMIN)
  @ApiOperation({ summary: 'Update Student details (Admin only)' })
  async update(@Param('id') id: string, @Body() dto: UpdateStudentDto) {
    return this.studentService.update(id, dto);
  }

  @Delete(':id')
  @Roles(UserRole.SUPER_ADMIN, UserRole.ADMIN)
  @HttpCode(HttpStatus.OK)
  @ApiOperation({ summary: 'Deactivate/Delete Student account (Admin only)' })
  async remove(@Param('id') id: string) {
    return this.studentService.remove(id);
  }
}
