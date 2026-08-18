const fs = require('fs');
const path = require('path');
const file = path.join(__dirname, 'apps/web/src/app/page.tsx');
let content = fs.readFileSync(file, 'utf8');

// 1. Interfaces
content = content.replace(
  /interface UserSession \{/g,
  interface School {\n  id: string;\n  name: string;\n  domain: string;\n  address: string;\n}\n\ninterface UserSession {
);
content = content.replace(
  /role: 'ADMIN' \| 'TREASURER' \| 'KORLAS' \| 'STUDENT' \| 'PARENT';/g,
  ole: 'SYS_ADMIN' | 'SCHOOL_ADMIN' | 'TREASURER' | 'KORLAS' | 'STUDENT' | 'PARENT';\n  schoolId?: string;
);
content = content.replace(
  /interface Transaction \{[\s\S]*?id: string;/g,
  interface Transaction {\n  id: string;\n  schoolId: string;
);
content = content.replace(
  /interface StudentBilling \{[\s\S]*?id: string;/g,
  interface StudentBilling {\n  id: string;\n  schoolId: string;
);

// 2. States
content = content.replace(
  /export default function DashboardPage\(\) \{/g,
  export default function DashboardPage() {\n  const [schools] = useState<School[]>([\n    { id: 's-1', name: 'SD Negeri 08 PAGI', domain: 'sdn08pagi.sch.id', address: 'Jl. Merdeka No. 8, Jakarta' },\n    { id: 's-2', name: 'SMP Negeri 1 Jakarta', domain: 'smpn1.sch.id', address: 'Jl. Pemuda No. 1, Jakarta' }\n  ]);\n  const [activeSchoolId, setActiveSchoolId] = useState<string>('s-1');\n  const activeSchool = schools.find(s => s.id === activeSchoolId) || schools[0];\n
);

// 3. Mock Data Updates
content = content.replace(
  /const \[userAccounts, setUserAccounts\] = useState<UserAccount\[\]>\(\[/g,
  const [userAccounts, setUserAccounts] = useState<UserAccount[]>([\n    { schoolId: 's-0', id: 'u-sys-1', name: 'System Admin', email: 'sysadmin@sistemkas.com', role: 'SYS_ADMIN', status: 'ACTIVE' },
);
content = content.replace(/\{ id: 'u-/g, { schoolId: 's-1', id: 'u-);
content = content.replace(/role: 'ADMIN'/g, ole: 'SCHOOL_ADMIN');
content = content.replace(/\{ id: 'tx-/g, { schoolId: 's-1', id: 'tx-);
content = content.replace(/\{ id: 'b-/g, { schoolId: 's-1', id: 'b-);

// 4. Scoped Filters
content = content.replace(
  /const scopedTransactions = transactions\.filter\(\(t\) => \{/g,
  const scopedTransactions = transactions.filter((t) => {\n    if (t.schoolId !== activeSchoolId) return false;
);
content = content.replace(
  /const scopedBillings = billings\.filter\(\(b\) => \{/g,
  const scopedBillings = billings.filter((b) => {\n    if (b.schoolId !== activeSchoolId) return false;
);
content = content.replace(
  /const scopedStudents = userAccounts\.filter\(\(u\) => \{/g,
  const scopedStudents = userAccounts.filter((u) => {\n    if (u.schoolId !== activeSchoolId) return false;
);
content = content.replace(
  /const registeredStudents = userAccounts\.filter\(\(u\) => u\.role === 'STUDENT'\);/g,
  const registeredStudents = userAccounts.filter((u) => u.role === 'STUDENT' && u.schoolId === activeSchoolId);
);
content = content.replace(
  /const targetStudentProfile = userAccounts\.find\(\(u\) => \{/g,
  const targetStudentProfile = userAccounts.find((u) => { if (u.schoolId !== activeSchoolId) return false;
);

// 5. New Transactions/Billings push with schoolId
content = content.replace(
  /const newTx: Transaction = \{/g,
  const newTx: Transaction = {\n      schoolId: activeSchoolId,
);
content = content.replace(
  /id: \	x-excess-\\\$\\{Date\.now\(\)\\}\,/g,
  id: \	x-excess-\\\$\\{Date.now()\\}\,\n        schoolId: activeSchoolId,
);
content = content.replace(
  /const newBillings: StudentBilling\[\] = selectedStudents\.map\(\(s, i\) => \(\{/g,
  const newBillings: StudentBilling[] = selectedStudents.map((s, i) => ({\n      schoolId: activeSchoolId,
);
content = content.replace(
  /const newStudentAccount: UserAccount = \{/g,
  const newStudentAccount: UserAccount = {\n      schoolId: activeSchoolId,
);
content = content.replace(
  /const newUser: UserAccount = \{/g,
  const newUser: UserAccount = {\n      schoolId: activeSchoolId,
);

// 6. Login logic
content = content.replace(
  /setCurrentUser\(\{\n          id: data\.user\.id,/g,
  setActiveSchoolId(data.user.schoolId || 's-1');\n        setCurrentUser({\n          id: data.user.id,
);
content = content.replace(
  /const foundUser = userAccounts\.find\(\(u\) => u\.email === loginEmail\);/g,
  const foundUser = userAccounts.find((u) => u.email === loginEmail);\n      if (foundUser && foundUser.schoolId) setActiveSchoolId(foundUser.schoolId);
);
content = content.replace(
  /role: data\.user\.role,/g,
  ole: data.user.role,\n          schoolId: data.user.schoolId,
);
content = content.replace(
  /role: foundUser\.role,/g,
  ole: foundUser.role,\n          schoolId: foundUser.schoolId,
);
content = content.replace(
  /setCurrentUser\(\{ id: 'u-1', name: 'Budi Santoso', email: loginEmail, role: 'SCHOOL_ADMIN', token: 'jwt-token-admin' \}\);/g,
  setActiveSchoolId('s-1');\n      setCurrentUser({ id: 'u-1', name: 'Budi Santoso', email: loginEmail, role: 'SCHOOL_ADMIN', schoolId: 's-1', token: 'jwt-token-admin' });
);
content = content.replace(
  /setCurrentUser\(\{ id: 'u-2', name: 'Siti Rahma', email: loginEmail, role: 'TREASURER', token: 'jwt-token-treasurer' \}\);/g,
  setActiveSchoolId('s-1');\n      setCurrentUser({ id: 'u-2', name: 'Siti Rahma', email: loginEmail, role: 'TREASURER', schoolId: 's-1', token: 'jwt-token-treasurer' });
);
content = content.replace(
  /setCurrentUser\(\{ id: 'u-korlas', name: 'Korlas 5-A', email: loginEmail, role: 'KORLAS', managedClass: 'Kelas 5-A', token: 'jwt-token-korlas' \}\);/g,
  setActiveSchoolId('s-1');\n      setCurrentUser({ id: 'u-korlas', name: 'Korlas 5-A', email: loginEmail, role: 'KORLAS', managedClass: 'Kelas 5-A', schoolId: 's-1', token: 'jwt-token-korlas' });
);
content = content.replace(
  /setCurrentUser\(\{ id: 'u-3', name: 'Andi Wijaya', email: loginEmail, role: 'STUDENT', className: 'Kelas 5-A', token: 'jwt-token-student' \}\);/g,
  setActiveSchoolId('s-1');\n      setCurrentUser({ id: 'u-3', name: 'Andi Wijaya', email: loginEmail, role: 'STUDENT', className: 'Kelas 5-A', schoolId: 's-1', token: 'jwt-token-student' });
);
content = content.replace(
  /setCurrentUser\(\{ id: 'u-6', name: 'Wali Murid Andi Wijaya', email: loginEmail, role: 'PARENT', studentId: 'u-3', className: 'Kelas 5-A', token: 'jwt-token-parent' \}\);/g,
  setActiveSchoolId('s-1');\n      setCurrentUser({ id: 'u-6', name: 'Wali Murid Andi Wijaya', email: loginEmail, role: 'PARENT', studentId: 'u-3', className: 'Kelas 5-A', schoolId: 's-1', token: 'jwt-token-parent' });
);

// Add SYS_ADMIN login shortcut
content = content.replace(
  /<button onClick=\{\(\) => \{ setLoginEmail\('admin@sdn08pagi\.sch\.id'\);/g,
  <button onClick={() => { setLoginEmail('sysadmin@sistemkas.com'); setLoginPassword('Password123!'); }} className="col-span-2 p-2.5 rounded-xl bg-slate-800 text-white font-bold text-[11px] text-center shadow-lg">?? 0. System Admin</button>\n                <button onClick={() => { setLoginEmail('admin@sdn08pagi.sch.id');
);
content = content.replace(
  /if \(loginEmail === 'sysadmin@sistemkas\.com'\)/g,
  // // avoid conflicts if re-running
);
content = content.replace(
  /if \(loginEmail === 'admin@sdn08pagi\.sch\.id'\) \{/g,
  if (loginEmail === 'sysadmin@sistemkas.com') {\n      setActiveSchoolId('s-1');\n      setCurrentUser({ id: 'u-sys-1', name: 'System Admin', email: loginEmail, role: 'SYS_ADMIN', schoolId: 's-0', token: 'jwt-token-sysadmin' });\n      setIsLoggingIn(false);\n      return;\n    }\n    if (loginEmail === 'admin@sdn08pagi.sch.id') {
);

// 7. Dynamic School Texts
content = content.replace(/SD Negeri 08 PAGI/g, \{activeSchool.name\});
// Fix cases where it's inside strings or templates
content = content.replace(/'\{activeSchool\.name\}'/g, 'activeSchool.name');
content = content.replace(/"\{activeSchool\.name\}"/g, 'activeSchool.name');
content = content.replace(/\\{activeSchool\.name\}\/g, 'activeSchool.name');
content = content.replace(/>\{activeSchool\.name\}</g, '>{activeSchool.name}<'); // Valid JSX
content = content.replace(/sdn08pagi\.sch\.id/g, \);

// 8. Navbar / Header School Switcher (For SYS_ADMIN)
content = content.replace(
  /<h2 className="text-xl font-black text-slate-800 hidden md:block">Portal Keuangan<\/h2>/g,
  <h2 className="text-xl font-black text-slate-800 hidden md:block">Portal Keuangan</h2>\n            {currentUser?.role === 'SYS_ADMIN' && (\n              <div className="ml-4 flex items-center bg-indigo-50 border border-indigo-200 rounded-xl px-3 py-1.5 shadow-sm">\n                <span className="text-xs font-bold text-indigo-800 mr-2">Pilih Sekolah:</span>\n                <select value={activeSchoolId} onChange={(e) => setActiveSchoolId(e.target.value)} className="bg-transparent text-indigo-900 text-sm font-bold focus:outline-none">\n                  {schools.map(s => (\n                    <option key={s.id} value={s.id}>{s.name}</option>\n                  ))}\n                </select>\n              </div>\n            )}
);

// 9. Hide options/features for SCHOOL_ADMIN if they were ADMIN
content = content.replace(
  /currentUser\.role === 'ADMIN'/g,
  (currentUser.role === 'SCHOOL_ADMIN' || currentUser.role === 'SYS_ADMIN')
);
content = content.replace(
  /newUserRole === 'ADMIN'/g,
  
ewUserRole === 'SCHOOL_ADMIN'
);
content = content.replace(
  /<option value="ADMIN">ADMIN \(Administrator\)<\/option>/g,
  <option value="SCHOOL_ADMIN">SCHOOL_ADMIN (Admin Sekolah)</option>
);
content = content.replace(
  /role: 'ADMIN' \|/g,
  ole: 'SYS_ADMIN' | 'SCHOOL_ADMIN' | // Handled by first replacement, just in case
);

// Fix initial login hero text
content = content.replace(/\{activeSchool\.name\} Showcase/g, SDN 08 Pagi Showcase);
content = content.replace(/>Sekolah Dasar Negeri</g, >{activeSchool ? 'Portal Keuangan' : 'Sekolah Dasar Negeri'}<);

fs.writeFileSync(file, content, 'utf8');
console.log('Migration completed successfully');
