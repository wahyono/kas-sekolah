const { PrismaClient } = require('@prisma/client');
const argon2 = require('argon2');

const prisma = new PrismaClient();

async function main() {
  const passwordHash = await argon2.hash('Password123!');
  
  // Find or Create School
  let school = await prisma.school.findUnique({ where: { code: 'sistemkas' } });
  if (!school) {
    school = await prisma.school.create({
      data: {
        name: 'Pusat Sistem Kas',
        code: 'sistemkas',
      }
    });
  }

  const sysadmin = await prisma.user.upsert({
    where: { email: 'sysadmin@sistemkas.com' },
    update: {
      passwordHash,
      role: 'SUPER_ADMIN'
    },
    create: {
      schoolId: school.id,
      name: 'System Admin',
      email: 'sysadmin@sistemkas.com',
      passwordHash,
      role: 'SUPER_ADMIN',
    },
  });
  console.log('SysAdmin User:', sysadmin.email, 'Password: Password123!');
  
  const schoolAdmin = await prisma.user.upsert({
    where: { email: 'admin@sdn08pagi.sch.id' },
    update: {
      passwordHash,
      role: 'ADMIN'
    },
    create: {
      schoolId: school.id,
      name: 'Budi Santoso',
      email: 'admin@sdn08pagi.sch.id',
      passwordHash,
      role: 'ADMIN',
    },
  });
  console.log('School Admin:', schoolAdmin.email, 'Password: Password123!');
}

main()
  .catch(e => console.error(e))
  .finally(() => prisma.$disconnect());
