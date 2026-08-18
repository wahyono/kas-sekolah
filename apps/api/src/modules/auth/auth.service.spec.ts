import { Test, TestingModule } from '@nestjs/testing';
import { AuthService } from './auth.service';
import { PrismaService } from '../../core/database/prisma.service';
import { JwtService } from '@nestjs/jwt';
import { UnauthorizedException, NotFoundException } from '@nestjs/common';
import * as argon2 from 'argon2';

describe('AuthService Unit Tests', () => {
  let service: AuthService;
  let prisma: PrismaService;

  const mockPrismaService = {
    user: {
      findUnique: jest.fn(),
      create: jest.fn(),
    },
    school: {
      findUnique: jest.fn(),
    },
  };

  const mockJwtService = {
    sign: jest.fn().mockReturnValue('mocked-jwt-token'),
  };

  beforeEach(async () => {
    const module: TestingModule = await Test.createTestingModule({
      providers: [
        AuthService,
        { provide: PrismaService, useValue: mockPrismaService },
        { provide: JwtService, useValue: mockJwtService },
      ],
    }).compile();

    service = module.get<AuthService>(AuthService);
    prisma = module.get<PrismaService>(PrismaService);
  });

  it('should be defined', () => {
    expect(service).toBeDefined();
  });

  describe('login', () => {
    it('should throw UnauthorizedException if user is not found', async () => {
      mockPrismaService.user.findUnique.mockResolvedValue(null);

      await expect(
        service.login({ email: 'nonexistent@test.com', password: 'password' }),
      ).rejects.toThrow(UnauthorizedException);
    });

    it('should return access token and user info on valid credentials', async () => {
      const hashedPassword = await argon2.hash('Password123!');
      const mockUser = {
        id: 'user-uuid-1',
        email: 'admin@test.com',
        passwordHash: hashedPassword,
        isActive: true,
        name: 'Admin User',
        role: 'ADMIN',
        schoolId: 'school-uuid-1',
      };

      mockPrismaService.user.findUnique.mockResolvedValue(mockUser);

      const result = await service.login({ email: 'admin@test.com', password: 'Password123!' });

      expect(result).toHaveProperty('accessToken', 'mocked-jwt-token');
      expect(result.user.email).toBe('admin@test.com');
    });
  });
});
