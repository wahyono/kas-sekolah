import { ApiProperty } from '@nestjs/swagger';
import { IsNotEmpty, IsNumber, IsOptional, IsString, IsUUID, Min } from 'class-validator';

export class CreateCashAccountDto {
  @ApiProperty({ example: '3ba4c752-6b94-4d87-94d0-8bc2df9e59cf', description: 'Class UUID' })
  @IsUUID('4')
  @IsNotEmpty()
  classId!: string;

  @ApiProperty({ example: 'Kas Utama Kelas 10-IPA-1', description: 'Account Name' })
  @IsString()
  @IsNotEmpty()
  name!: string;

  @ApiProperty({ example: 0, description: 'Initial opening balance', default: 0 })
  @IsOptional()
  @IsNumber()
  @Min(0)
  initialBalance?: number;
}
