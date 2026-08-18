import { Controller, Get, Patch, Delete, Param, Body, UseGuards, Query, Req } from '@nestjs/common';
import { ApiTags, ApiOperation, ApiBearerAuth, ApiQuery } from '@nestjs/swagger';
import { UserService } from './user.service';
import { UpdateUserDto } from './dto/update-user.dto';
import { JwtAuthGuard } from '../../core/guards/jwt-auth.guard';
import { RolesGuard } from '../../core/guards/roles.guard';
import { Roles } from '../../core/decorators/roles.decorator';
import { UserRole } from '@prisma/client';

@ApiTags('User Management')
@ApiBearerAuth()
@UseGuards(JwtAuthGuard, RolesGuard)
@Controller('users')
export class UserController {
  constructor(private readonly userService: UserService) {}

  @Get()
  @Roles(UserRole.SUPER_ADMIN, UserRole.ADMIN, UserRole.KORLAS)
  @ApiOperation({ summary: 'Get all users' })
  @ApiQuery({ name: 'schoolId', required: false })
  @ApiQuery({ name: 'search', required: false })
  async findAll(@Req() req: any, @Query('schoolId') schoolId?: string, @Query('search') search?: string) {
    return this.userService.findAll(req.user.role, schoolId, search, req.user.id);
  }

  @Get(':id')
  @Roles(UserRole.SUPER_ADMIN, UserRole.ADMIN)
  @ApiOperation({ summary: 'Get user by id' })
  async findOne(@Param('id') id: string) {
    return this.userService.findOne(id);
  }

  @Patch(':id')
  @Roles(UserRole.SUPER_ADMIN, UserRole.ADMIN)
  @ApiOperation({ summary: 'Update user' })
  async update(@Param('id') id: string, @Body() dto: UpdateUserDto) {
    return this.userService.update(id, dto);
  }

  @Delete(':id')
  @Roles(UserRole.SUPER_ADMIN, UserRole.ADMIN)
  @ApiOperation({ summary: 'Delete user' })
  async remove(@Param('id') id: string) {
    return this.userService.remove(id);
  }
}
