const fs = require('fs');
let code = fs.readFileSync('src/app/page.tsx', 'utf8');

const roleFormatter = `
  const formatRole = (role?: string) => {
    switch(role) {
      case 'SUPER_ADMIN': return 'Super Admin';
      case 'ADMIN': return 'Admin Sekolah';
      case 'TREASURER': return 'Bendahara';
      case 'KORLAS': return 'Koordinator Kelas';
      case 'PARENT': return 'Wali Murid';
      case 'STUDENT': return 'Siswa';
      default: return role || '';
    }
  };
`;

code = code.replace(
  'const korlasClassName = currentUser?.role === \\'KORLAS\\' ? (classes.find((c: any) => c.id === currentUser.managedClass)?.name || currentUser.managedClass || \\'Kelas Anda\\') : \\'\\';',
  'const korlasClassName = currentUser?.role === \\'KORLAS\\' ? (classes.find((c: any) => c.id === currentUser.managedClass)?.name || currentUser.managedClass || \\'Kelas Anda\\') : \\'\\';\\n' + roleFormatter
);

code = code.replace(/\\{currentUser\\.role\\} \\{currentUser\\.managedClass/g, '{formatRole(currentUser.role)} {currentUser.managedClass');
code = code.replace(/\\<td className=\"p-3\">\\<span className=\"font-bold text-indigo-700\">\\{u\\.role\\}<\\/span><\\/td>/g, '<td className=\"p-3\"><span className=\"font-bold text-indigo-700\">{formatRole(u.role)}</span></td>');

fs.writeFileSync('src/app/page.tsx', code);
console.log('Fixed page.tsx');
