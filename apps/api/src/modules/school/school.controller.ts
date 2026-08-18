import { Controller, Get, Post, Body, Patch, Param, Delete, UseGuards } from '@nestjs/common';
import { ApiTags, ApiOperation, ApiResponse, ApiBearerAuth } from '@nestjs/swagger';
import { SchoolService } from './school.service';
import { CreateSchoolDto } from './dto/create-school.dto';
import { UpdateSchoolDto } from './dto/update-school.dto';

@ApiTags('Schools')
@Controller('schools')
export class SchoolController {
  constructor(private readonly schoolService: SchoolService) {}

  @Post()
  @ApiOperation({ summary: 'Create a new school' })
  @ApiResponse({ status: 201, description: 'The school has been successfully created.' })
  create(@Body() createSchoolDto: CreateSchoolDto) {
    return this.schoolService.create(createSchoolDto);
  }

  @Get()
  @ApiOperation({ summary: 'Get all schools' })
  @ApiResponse({ status: 200, description: 'Return all schools.' })
  findAll() {
    return this.schoolService.findAll();
  }

  @Get(':id')
  @ApiOperation({ summary: 'Get a school by id' })
  @ApiResponse({ status: 200, description: 'Return the school.' })
  findOne(@Param('id') id: string) {
    return this.schoolService.findOne(id);
  }

  @Patch(':id')
  @ApiOperation({ summary: 'Update a school' })
  @ApiResponse({ status: 200, description: 'The school has been successfully updated.' })
  update(@Param('id') id: string, @Body() updateSchoolDto: UpdateSchoolDto) {
    return this.schoolService.update(id, updateSchoolDto);
  }

  @Delete(':id')
  @ApiOperation({ summary: 'Delete a school' })
  @ApiResponse({ status: 200, description: 'The school has been successfully deleted.' })
  remove(@Param('id') id: string) {
    return this.schoolService.remove(id);
  }
}
