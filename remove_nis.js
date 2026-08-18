const fs = require('fs');
const path = require('path');
const file = path.join(__dirname, 'apps/web/src/app/page.tsx');
let content = fs.readFileSync(file, 'utf8');

// 1. Types
content = content.replace(/  nis\?: string;\n/g, '');
content = content.replace(/  nisn\?: string;\n/g, '');
content = content.replace(/  religion\?: string;\n/g, '');
content = content.replace(/  nis: string;\n/g, '');
content = content.replace(/  nisn: string;\n/g, '');

// 2. States
content = content.replace(/  const \[studentNis, setStudentNis\] = useState<string>\('.*?'\);\n/g, '');
content = content.replace(/  const \[studentNisn, setStudentNisn\] = useState<string>\('.*?'\);\n/g, '');
content = content.replace(/  const \[studentReligion, setStudentReligion\] = useState<string>\('.*?'\);\n/g, '');

// 3. handleDirectStudentAdd validation
content = content.replace(/if \(!studentNis \|\| !studentNisn \|\| !studentName\) return;/g, 'if (!studentName) return;');
content = content.replace(/const studentEmailGenerated = studentEmail \|\| \\$\{studentNis\}@sdn08pagi\.sch\.id\;/g, 'const studentEmailGenerated = studentEmail || ${studentName.toLowerCase().replace(/\\s+/g, "")}@sdn08pagi.sch.id;');

// 4. Object properties removals
// Initial userAccounts
content = content.replace(/, nis: '[^']*', nisn: '[^']*'/g, '');
content = content.replace(/, religion: '[^']*'/g, '');

// Initial billings
content = content.replace(/, nis: '[^']*', nisn: '[^']*'/g, '');

// Login fallback & others
content = content.replace(/          nis: data\.user\.nis,\n/g, '');
content = content.replace(/          nisn: data\.user\.nisn,\n/g, '');
content = content.replace(/          nis: foundUser\.nis,\n/g, '');
content = content.replace(/          nisn: foundUser\.nisn,\n/g, '');

// handleDirectStudentAdd new user push
content = content.replace(/      nis: studentNis,\n/g, '');
content = content.replace(/      nisn: studentNisn,\n/g, '');
content = content.replace(/      religion: studentReligion,\n/g, '');

// handleDirectStudentAdd clearing state
content = content.replace(/    setStudentNis\(''\);\n/g, '');
content = content.replace(/    setStudentNisn\(''\);\n/g, '');

// create scheme billing push
content = content.replace(/      nis: s\.nis \|\| '20260501',\n/g, '');
content = content.replace(/      nisn: s\.nisn \|\| '0051234567',\n/g, '');

// transaction description
content = content.replace(/ \(NIS: \$\{selectedBilling\.nis\}\)/g, '');

// PDF generation headers
content = content.replace(/'NIS', 'NISN', 'Nama Siswa'/g, "'Nama Siswa'");
content = content.replace(/          b\.nis,\n          b\.nisn,\n/g, '');

// Filter logic
content = content.replace(/ \|\| u\.nis === currentUser\?\.nis/g, '');
content = content.replace(/ \|\| b\.nis === targetStudentProfile\.nis/g, '');
content = content.replace(/ \|\| b\.nis === selectedStudentDetail\.nis/g, '');

// Dashboard currentUser mock
content = content.replace(/    nis: currentUser\?\.nis \|\| '20260501',\n/g, '');
content = content.replace(/    nisn: currentUser\?\.nisn \|\| '0051234567',\n/g, '');
content = content.replace(/    religion: 'ISLAM',\n/g, '');

// View Student details (Student Details Profile block)
content = content.replace(/<span className="text-slate-500 font-medium">NIS \(Nomor Induk\):<\/span>\s*<span className="font-mono font-bold text-indigo-600">\{targetStudentProfile\.nis \|\| '-'}<\/span>/g, '');
content = content.replace(/<span className="text-slate-500 font-medium">NISN \(10 Digit\):<\/span>\s*<span className="font-mono font-semibold text-slate-900">\{targetStudentProfile\.nisn \|\| '-'}<\/span>/g, '');
content = content.replace(/<span className="text-slate-500 font-medium">Agama:<\/span>\s*<span className="font-semibold text-slate-900">\{targetStudentProfile\.religion \|\| '-'}<\/span>/g, '');

// View Student modal (Profile detail modal)
content = content.replace(/<div><span className="text-slate-400">NIS:<\/span> <strong className="text-indigo-600">\{targetStudentProfile\.nis \|\| '-'}<\/strong><\/div>/g, '');
content = content.replace(/<div><span className="text-slate-400">NISN:<\/span> <strong>\{targetStudentProfile\.nisn \|\| '-'}<\/strong><\/div>/g, '');
content = content.replace(/<div><span className="text-slate-400">Agama:<\/span> <strong>\{targetStudentProfile\.religion \|\| '-'}<\/strong><\/div>/g, '');

// User modal (Table)
content = content.replace(/<th className="p-3">NIS<\/th>\s*<th className="p-3">NISN<\/th>/g, '');
content = content.replace(/<th className="p-3">Agama<\/th>/g, '');
content = content.replace(/<td className="p-3 font-mono font-semibold text-indigo-600">\{s\.nis \|\| '-'}<\/td>\s*<td className="p-3 font-mono text-slate-500">\{s\.nisn \|\| '-'}<\/td>/g, '');
content = content.replace(/<td className="p-3">\{s\.religion \|\| '-'}<\/td>/g, '');

// User modal role display fallback
content = content.replace(/ \? \NIS: \$\{u\.nis\}\ /g, ''); // Fix this regex, it's u.nis ? \NIS: \\ : '-'

// Detail student modal (View user)
content = content.replace(/<div><p className="text-slate-500">NIS:<\/p><p className="font-mono font-bold text-indigo-600">\{selectedStudentDetail\.nis \|\| '-'}<\/p><\/div>/g, '');
content = content.replace(/<div><p className="text-slate-500">NISN:<\/p><p className="font-mono font-semibold">\{selectedStudentDetail\.nisn \|\| '-'}<\/p><\/div>/g, '');
content = content.replace(/<div><p className="text-slate-500">Agama:<\/p><p className="font-semibold">\{selectedStudentDetail\.religion \|\| '-'}<\/p><\/div>/g, '');

// Billing table
content = content.replace(/<th className="p-3">NIS<\/th>\s*<th className="p-3">NISN<\/th>/g, '');
content = content.replace(/<td className="p-3 font-mono font-semibold text-indigo-600">\{b\.nis\}<\/td>\s*<td className="p-3 font-mono text-slate-500">\{b\.nisn\}<\/td>/g, '');

// Forms: NIS, NISN, Agama
content = content.replace(/<label className="block text-slate-700 font-bold mb-1">NIS<\/label>[\s\S]*?onChange=\{\(e\) => setStudentNis\(e\.target\.value\)\}[\s\S]*?\/>/g, '');
content = content.replace(/<label className="block text-slate-700 font-bold mb-1">NISN \(10 Digit\)<\/label>[\s\S]*?onChange=\{\(e\) => setStudentNisn\(e\.target\.value\)\}[\s\S]*?\/>/g, '');
content = content.replace(/<label className="block text-slate-700 font-bold mb-1">Agama<\/label>[\s\S]*?<\/select>/g, '');

fs.writeFileSync(file, content, 'utf8');
console.log('Replacements done');
