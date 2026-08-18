import { Test, TestingModule } from '@nestjs/testing';
import { StudentService } from './student.service';
import { PrismaService } from '../../core/database/prisma.service';
import { ConflictException, NotFoundException } from '@nestjs/common';
import { Gender, Religion, UserRole } from '@prisma/client';

describe('StudentService Unit Tests', () => {
  let service: StudentService;
  let prisma: PrismaService;

  const mockPrismaService = {
    user: {
      findFirst: jest.fn(),
      findUnique: jest.fn(),
      findMany: jest.fn(),
      create: jest.fn(),
      update: jest.fn(),
    },
  };

  beforeEach(async () => {
    const module: TestingModule = await Test.createTestingModule({
      providers: [
        StudentService,
        { provide: PrismaService, useValue: mockPrismaService },
      ],
    }).compile();

    service = module.get<StudentService>(StudentService);
    prisma = module.get<PrismaService>(PrismaService);
    jest.clearAllMocks();
  });

  it('should be defined', () => {
    expect(service).toBeDefined();
  });

  describe('create', () => {
    const dto = {
      schoolId: 'school-uuid',
      nis: '20261001',
      nisn: '0051234567',
      name: 'Budi Kurniawan',
      email: 'budi@student.sch.id',
      gender: Gender.MALE,
      religion: Religion.ISLAM,
      password: 'Password123!',
    };

    it('should throw ConflictException if NIS already exists', async () => {
      mockPrismaService.user.findFirst.mockResolvedValueOnce({ id: 'existing-nis-id' });

      await expect(service.create(dto)).rejects.toThrow(ConflictException);
    });

    it('should throw ConflictException if NISN already exists', async () => {
      mockPrismaService.user.findFirst
        .mockResolvedValueOnce(null) // NIS ok
        .mockResolvedValueOnce({ id: 'existing-nisn-id' }); // NISN exists

      await expect(service.create(dto)).rejects.toThrow(ConflictException);
    });

    it('should create student when NIS and NISN are unique', async () => {
      mockPrismaService.user.findFirst.mockResolvedValue(null);
      mockPrismaService.user.findUnique.mockResolvedValue(null);
      mockPrismaService.user.create.mockResolvedValue({
        id: 'new-student-id',
        ...dto,
        role: UserRole.STUDENT,
        isActive: true,
      });

      const result = await service.create(dto);
      expect(result).toHaveProperty('nis', '20261001');
      expect(result).toHaveProperty('nisn', '0051234567');
      expect(result).toHaveProperty('gender', Gender.MALE);
    });
  });
});
