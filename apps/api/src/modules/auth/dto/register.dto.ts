import { ApiProperty, ApiPropertyOptional } from '@nestjs/swagger';
import { UserRole } from '@prisma/client';
import { IsEmail, IsEnum, IsNotEmpty, IsOptional, IsString, IsUUID, MinLength } from 'class-validator';

export class RegisterDto {
  @ApiPropertyOptional({ example: '3ba4c752-6b94-4d87-94d0-8bc2df9e59cf', description: 'School UUID' })
  @IsUUID('4', { message: 'School ID must be a valid UUID' })
  @IsOptional()
  schoolId?: string;

  @ApiProperty({ example: 'Ahmad Treasurer', description: 'Full user name' })
  @IsString()
  @IsNotEmpty()
  name!: string;

  @ApiProperty({ example: 'bendahara@sdn08pagi.sch.id', description: 'User email' })
  @IsEmail({}, { message: 'Invalid email address format' })
  @IsNotEmpty()
  email!: string;

  @ApiPropertyOptional({ example: '+628123456789', description: 'User phone number' })
  @IsOptional()
  @IsString()
  phone?: string;

  @ApiProperty({ example: 'SecurePassword123!', description: 'User password' })
  @IsString()
  @IsNotEmpty()
  @MinLength(6, { message: 'Password must be at least 6 characters long' })
  password!: string;

  @ApiProperty({ enum: UserRole, example: UserRole.TREASURER, description: 'User System Role' })
  @IsEnum(UserRole)
  @IsNotEmpty()
  role!: UserRole;

  @ApiPropertyOptional({ example: '3ba4c752-6b94-4d87-94d0-8bc2df9e59cf', description: 'Associated Student UUID (Required for PARENT role)' })
  @IsOptional()
  @IsUUID('4')
  studentId?: string;

  @ApiPropertyOptional({ example: '20260501', description: 'Student NIS' })
  @IsOptional()
  @IsString()
  nis?: string;

  @ApiPropertyOptional({ example: '0051234567', description: 'Student NISN' })
  @IsOptional()
  @IsString()
  nisn?: string;

  @ApiPropertyOptional({ example: 'class-uuid', description: 'Class UUID for student enrollment' })
  @IsOptional()
  @IsUUID('4')
  enrolledClassId?: string;

  @ApiPropertyOptional({ example: 'class-uuid', description: 'Class UUID managed by KORLAS' })
  @IsOptional()
  @IsUUID('4')
  managedClassId?: string;

  @ApiPropertyOptional({ enum: ['MALE', 'FEMALE'], example: 'MALE', description: 'Student gender' })
  @IsOptional()
  @IsEnum(['MALE', 'FEMALE'])
  gender?: 'MALE' | 'FEMALE';
}
