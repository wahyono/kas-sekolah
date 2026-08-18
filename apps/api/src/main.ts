import { NestFactory } from '@nestjs/core';
import { ValidationPipe, Logger } from '@nestjs/common';
import { DocumentBuilder, SwaggerModule } from '@nestjs/swagger';
import { AppModule } from './app.module';
import { HttpExceptionFilter } from './core/filters/http-exception.filter';

async function bootstrap() {
  const logger = new Logger('Bootstrap');
  const app = await NestFactory.create(AppModule);

  // Global Prefix
  app.setGlobalPrefix('api/v1');

  // CORS
  app.enableCors({
    origin: true,
    credentials: true,
  });

  // Global Pipes & Filters
  app.useGlobalPipes(
    new ValidationPipe({
      whitelist: true,
      forbidNonWhitelisted: true,
      transform: true,
      transformOptions: { enableImplicitConversion: true },
    }),
  );
  app.useGlobalFilters(new HttpExceptionFilter());

  // Swagger Documentation Setup
  const config = new DocumentBuilder()
    .setTitle('Kas Sekolah Enterprise API')
    .setDescription('School Cash Management & Double-Entry Financial Ledger Service API Specifications')
    .setVersion('1.0.0')
    .addBearerAuth()
    .build();

  const document = SwaggerModule.createDocument(app, config);
  SwaggerModule.setup('api/docs', app, document);

  const port = process.env.PORT || 3001;
  await app.listen(port);
  logger.log(`🚀 Kas Sekolah API is running on: http://localhost:${port}/api/v1`);
  logger.log(`📖 Swagger API Documentation available at: http://localhost:${port}/api/docs`);
}

bootstrap();
