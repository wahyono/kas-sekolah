import { ApiProperty } from '@nestjs/swagger';
import { IsNotEmpty, IsString, IsUUID } from 'class-validator';

export class CreateClassDto {
  @ApiProperty({ example: '10-IPA-1', description: 'Class Name' })
  @IsString()
  @IsNotEmpty()
  name!: string;

  @ApiProperty({ example: 'uuid', description: 'Academic Year ID' })
  @IsUUID('4')
  @IsNotEmpty()
  academicYearId!: string;
}
