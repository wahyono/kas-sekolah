import { IsString, IsNotEmpty, IsOptional } from 'class-validator';
import { ApiProperty, ApiPropertyOptional } from '@nestjs/swagger';

export class CreateSchoolDto {
  @ApiProperty({ example: 'SD Negeri 08 PAGI' })
  @IsString()
  @IsNotEmpty()
  name: string;

  @ApiProperty({ example: 'sdn08pagi.sch.id', description: 'Unique code/domain for the school' })
  @IsString()
  @IsNotEmpty()
  code: string;

  @ApiPropertyOptional({ example: 'Jl. Merdeka No. 8, Jakarta' })
  @IsOptional()
  @IsString()
  address?: string;

  @ApiPropertyOptional({ example: '021-1234567' })
  @IsOptional()
  @IsString()
  phone?: string;
}
