const fs = require('fs');
const path = require('path');
const file = path.join(__dirname, 'apps/web/src/app/page.tsx');
let content = fs.readFileSync(file, 'utf8');

// Fix remaining description
content = content.replace(/ \(NIS: 20260501\)/g, '');

// Fix studentEmailGenerated
content = content.replace(/const studentEmailGenerated = studentEmail \|\| \\\\$\\{studentNis\\}@sdn08pagi\\.sch\\.id\;/g, 'const studentEmailGenerated = studentEmail || ${studentName.toLowerCase().replace(/\\\\s+/g, "")}@sdn08pagi.sch.id;');

// Fix u.nis ? NIS:  : '-'
content = content.replace(/: u\.nis \? \NIS: \\\$\\{u\.nis\\}\ : '-'/g, ": '-'");

fs.writeFileSync(file, content, 'utf8');
