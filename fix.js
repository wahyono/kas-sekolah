const fs = require('fs');
const path = require('path');
const file = path.join(__dirname, 'apps/web/src/app/page.tsx');
let content = fs.readFileSync(file, 'utf8');

// 1. Fix schools mock data
content = content.replace(
  /name: activeSchool\.name, domain: '\$\{activeSchool\.domain\}'/g,
  "name: 'SD Negeri 08 PAGI', domain: 'sdn08pagi.sch.id'"
);

// 2. Fix loginEmail initialization
content = content.replace(
  /const \[loginEmail, setLoginEmail\] = useState<string>\('bendahara@\$\{activeSchool\.domain\}'\);/g,
  "const [loginEmail, setLoginEmail] = useState<string>('bendahara@sdn08pagi.sch.id');"
);

// 3. Fix userAccounts mock data
content = content.replace(/\$\{activeSchool\.domain\}/g, "sdn08pagi.sch.id");

// 4. Fix transactions mock data missing schoolId
content = content.replace(
  /id: 'tx-1',/g,
  "id: 'tx-1',\n      schoolId: 's-1',"
);
content = content.replace(
  /id: 'tx-2',/g,
  "id: 'tx-2',\n      schoolId: 's-1',"
);
content = content.replace(
  /id: 'tx-3',/g,
  "id: 'tx-3',\n      schoolId: 's-1',"
);

// 5. Fix type error on line 433 and 138
// In user creation form:
content = content.replace(
  /const \[newUserRole, setNewUserRole\] = useState<'ADMIN' \| 'TREASURER' \| 'KORLAS' \| 'PARENT'>\('KORLAS'\);/g,
  "const [newUserRole, setNewUserRole] = useState<'SCHOOL_ADMIN' | 'TREASURER' | 'KORLAS' | 'PARENT'>('KORLAS');"
);

fs.writeFileSync(file, content, 'utf8');
console.log('Fix applied successfully');
