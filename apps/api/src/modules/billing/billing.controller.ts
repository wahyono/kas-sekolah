import { Controller, Post, Get, Param, Body, UseGuards, Req } from '@nestjs/common';
import { ApiTags, ApiOperation, ApiBearerAuth } from '@nestjs/swagger';
import { BillingService } from './billing.service';
import { CreateDuesSchemeDto } from './dto/create-dues-scheme.dto';
import { JwtAuthGuard } from '../../core/guards/jwt-auth.guard';
import { RolesGuard } from '../../core/guards/roles.guard';
import { Roles } from '../../core/decorators/roles.decorator';
import { UserRole } from '@prisma/client';

@ApiTags('Student Dues & Billing')
@ApiBearerAuth()
@UseGuards(JwtAuthGuard, RolesGuard)
@Controller('billings')
export class BillingController {
  constructor(private readonly service: BillingService) {}

  @Post('dues-scheme')
  @Roles(UserRole.SUPER_ADMIN, UserRole.ADMIN, UserRole.KORLAS)
  @ApiOperation({ summary: 'Create a new dues scheme (e.g. Monthly Fee) and auto-generate billings for enrolled students' })
  async createScheme(@Body() dto: CreateDuesSchemeDto, @Req() req: any) {
    return this.service.createDuesSchemeAndGenerateBillings(dto, req.user.role, req.user.id);
  }

  @Get('student/:studentId')
  @ApiOperation({ summary: 'Get all dues and billing statuses for a student' })
  async getStudentBillings(@Param('studentId') studentId: string) {
    return this.service.getStudentBillings(studentId);
  }

  @Get('scheme/:schemeId')
  @ApiOperation({ summary: 'Get all student billing payment statuses for a scheme' })
  async getSchemeBillings(@Param('schemeId') schemeId: string) {
    return this.service.getBillingsByScheme(schemeId);
  }

  @Get('class/:classId')
  @ApiOperation({ summary: 'Get all billings for a specific class' })
  async getBillingsByClass(@Param('classId') classId: string) {
    return this.service.getBillingsByClass(classId);
  }

  @Get('school/:schoolId')
  @ApiOperation({ summary: 'Get all billings for a specific school' })
  async getBillingsBySchool(@Param('schoolId') schoolId: string, @Req() req: any) {
    return this.service.getBillingsBySchool(schoolId, req.user.role, req.user.id);
  }
}
