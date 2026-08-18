import { PartialType, ApiPropertyOptional } from '@nestjs/swagger';
import { IsString, IsOptional, IsEnum, IsBoolean } from 'class-validator';
import { UserRole } from '@prisma/client';
import { RegisterDto } from '../../auth/dto/register.dto';

export class UpdateUserDto extends PartialType(RegisterDto) {
  @ApiPropertyOptional({ description: 'Is the user active?' })
  @IsOptional()
  @IsBoolean()
  isActive?: boolean;
}
