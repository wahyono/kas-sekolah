import { ApiProperty } from '@nestjs/swagger';
import { IsDateString, IsNotEmpty, IsNumber, IsString, IsUUID, Min } from 'class-validator';

export class CreateDuesSchemeDto {
  @ApiProperty({ example: '3ba4c752-6b94-4d87-94d0-8bc2df9e59cf', description: 'Cash Account ID' })
  @IsUUID('4')
  @IsNotEmpty()
  cashAccountId!: string;

  @ApiProperty({ example: 'Iuran Kas Bulanan Agustus 2026', description: 'Title of dues' })
  @IsString()
  @IsNotEmpty()
  title!: string;

  @ApiProperty({ example: 20000, description: 'Dues amount per student' })
  @IsNumber()
  @Min(1000)
  amount!: number;

  @ApiProperty({ example: '2026-08-31T23:59:59.000Z', description: 'Payment Due Date' })
  @IsDateString()
  @IsNotEmpty()
  dueDate!: string;
}
