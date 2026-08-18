import { ApiProperty, ApiPropertyOptional } from '@nestjs/swagger';
import { Gender, Religion } from '@prisma/client';
import { IsEmail, IsEnum, IsNotEmpty, IsOptional, IsString, IsUUID, Matches, MinLength } from 'class-validator';

export class CreateStudentDto {
  @ApiProperty({ example: '3ba4c752-6b94-4d87-94d0-8bc2df9e59cf', description: 'School UUID' })
  @IsUUID('4')
  @IsNotEmpty()
  schoolId!: string;

  @ApiProperty({ example: '20261001', description: 'Nomor Induk Siswa (NIS)' })
  @IsString()
  @IsNotEmpty()
  nis!: string;

  @ApiProperty({ example: '0051234567', description: 'Nomor Induk Siswa Nasional (NISN - 10 Digits)' })
  @IsString()
  @IsNotEmpty()
  @Matches(/^[0-9]{10}$/, { message: 'NISN must be exactly 10 numeric digits' })
  nisn!: string;

  @ApiProperty({ example: 'Andi Wijaya', description: 'Student Full Name' })
  @IsString()
  @IsNotEmpty()
  name!: string;

  @ApiProperty({ example: 'andi.wijaya@student.sch.id', description: 'Student Email' })
  @IsEmail()
  @IsNotEmpty()
  email!: string;

  @ApiPropertyOptional({ example: '+628123456789', description: 'Student Phone Number' })
  @IsOptional()
  @IsString()
  phone?: string;

  @ApiProperty({ enum: Gender, example: Gender.MALE, description: 'Student Gender (MALE / FEMALE)' })
  @IsEnum(Gender)
  @IsNotEmpty()
  gender!: Gender;

  @ApiProperty({ enum: Religion, example: Religion.ISLAM, description: 'Student Religion' })
  @IsEnum(Religion)
  @IsNotEmpty()
  religion!: Religion;

  @ApiProperty({ example: 'Password123!', description: 'Student Default Password' })
  @IsString()
  @IsNotEmpty()
  @MinLength(6)
  password!: string;
}
