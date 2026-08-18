import { ApiProperty, ApiPropertyOptional } from '@nestjs/swagger';
import { TransactionType } from '@prisma/client';
import { IsEnum, IsNotEmpty, IsNumber, IsOptional, IsString, IsUUID, Min } from 'class-validator';

export class RecordTransactionDto {
  @ApiProperty({ example: '3ba4c752-6b94-4d87-94d0-8bc2df9e59cf', description: 'Target Cash Account UUID' })
  @IsUUID('4')
  @IsNotEmpty()
  cashAccountId!: string;

  @ApiPropertyOptional({ example: '3ba4c752-6b94-4d87-94d0-8bc2df9e59cf', description: 'Associated Student Billing ID (optional)' })
  @IsOptional()
  @IsUUID('4')
  billingId?: string;

  @ApiProperty({ enum: TransactionType, example: TransactionType.INCOME })
  @IsEnum(TransactionType)
  @IsNotEmpty()
  type!: TransactionType;

  @ApiProperty({ example: 25000, description: 'Transaction Amount' })
  @IsNumber()
  @Min(0.01)
  amount!: number;

  @ApiProperty({ example: 'Iuran Kas', description: 'Category' })
  @IsString()
  @IsNotEmpty()
  category!: string;

  @ApiProperty({ example: 'Pembayaran Iuran Kas Agustus 2026 oleh Budi', description: 'Description' })
  @IsString()
  @IsNotEmpty()
  description!: string;

  @ApiPropertyOptional({ example: 'https://s3.amazonaws.com/bucket/receipt.png', description: 'Receipt URL' })
  @IsOptional()
  @IsString()
  receiptUrl?: string;

  @ApiPropertyOptional({ example: 'Tunai', description: 'Payment Method' })
  @IsOptional()
  @IsString()
  paymentMethod?: string;
}
