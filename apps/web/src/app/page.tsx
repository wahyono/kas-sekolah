'use client';

import React, { useState, useEffect } from 'react';
import jsPDF from 'jspdf';
import autoTable from 'jspdf-autotable';

const API_URL = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:3001/api/v1';

interface School {
  id: string;
  name: string;
  code: string;
  address?: string;
}

interface UserSession {
  id: string;
  name: string;
  email: string;
  role: 'SUPER_ADMIN' | 'ADMIN' | 'TREASURER' | 'KORLAS' | 'STUDENT' | 'PARENT';
  schoolId?: string;
  className?: string;
  managedClass?: string; // Multi-tenancy class scope for Korlas
  studentId?: string;
  token: string;
}

const formatRole = (role?: string) => {
  switch (role) {
    case 'SUPER_ADMIN': return 'Super Admin';
    case 'ADMIN': return 'Admin Sekolah';
    case 'TREASURER': return 'Bendahara';
    case 'KORLAS': return 'Koordinator Kelas';
    case 'PARENT': return 'Wali Murid';
    case 'STUDENT': return 'Siswa';
    default: return role || '';
  }
};

interface UserAccount {
  id: string;
  name: string;
  email: string;
  role: 'SUPER_ADMIN' | 'ADMIN' | 'TREASURER' | 'KORLAS' | 'STUDENT' | 'PARENT';
  schoolId?: string;
  className?: string;
  managedClass?: any;
  studentId?: string;
  studentName?: string;
  gender?: 'MALE' | 'FEMALE';
  status: 'ACTIVE' | 'INACTIVE';
  enrollments?: any[];
}

interface Transaction {
  id: string;
  schoolId: string;
  type: 'INCOME' | 'EXPENSE';
  category: string;
  incomeSource?: string;
  description: string;
  className?: string; // Class attribution for multi-tenancy
  amount: number;
  recordedBy: string;
  paymentMethod: string;
  date: string;
}

interface StudentBilling {
  id: string;
  schoolId: string;
  studentId: string;
  studentName: string;
  className?: string; // Class attribution for multi-tenancy
  classId?: string;
  schemeTitle: string;
  period: string; // e.g. "Agustus 2026"
  amountDue: number;
  amountPaid: number;
  status: 'PAID' | 'PARTIAL' | 'PENDING';
  paidAt?: string;
  paymentMethod?: string;
}

export default function DashboardPage() {
  const [currentUser, setCurrentUser] = useState<UserSession | null>(null);
  const [schools, setSchools] = useState<School[]>([]);
  const [activeSchoolId, setActiveSchoolId] = useState<string>('');
  const activeSchool = schools.find(s => s.id === activeSchoolId) || null;

  const fetchSchools = async (userRole?: string) => {
    try {
      const res = await fetch(`${API_URL}/schools`);
      if (res.ok) {
        const data = await res.json();
        setSchools(data);
        const role = userRole || currentUser?.role;
        // Only auto-select if role is known and not SUPER_ADMIN
        if (data.length > 0 && role && role !== 'SUPER_ADMIN') {
          setActiveSchoolId((prev) => prev || data[0].id);
        }
      }
    } catch (error) {
      console.error('Failed to fetch schools', error);
    }
  };

  const fetchUsers = async (token: string, schoolId?: string) => {
    try {
      const url = schoolId ? `${API_URL}/users?schoolId=${schoolId}` : `${API_URL}/users`;
      const res = await fetch(url, {
        headers: {
          'Authorization': `Bearer ${token}`
        }
      });
      if (res.ok) {
        const data = await res.json();
        setUserAccounts(data);
      }
    } catch (error) {
      console.error('Failed to fetch users', error);
    }
  };

  const fetchAcademicYears = async (token: string, schoolId?: string) => {
    try {
      const url = schoolId ? `${API_URL}/academic-years?schoolId=${schoolId}` : `${API_URL}/academic-years`;
      const res = await fetch(url, { headers: { 'Authorization': `Bearer ${token}` } });
      if (res.ok) setAcademicYears(await res.json());
    } catch (error) {
      console.error('Failed to fetch academic years', error);
    }
  };

  const fetchClasses = async (token: string, academicYearId?: string, schoolId?: string) => {
    try {
      let url = `${API_URL}/classes`;
      const params = new URLSearchParams();
      if (academicYearId) params.append('academicYearId', academicYearId);
      if (schoolId) params.append('schoolId', schoolId);
      if (params.toString()) url += `?${params.toString()}`;
      
      const res = await fetch(url, { headers: { 'Authorization': `Bearer ${token}` } });
      if (res.ok) setClasses(await res.json());
    } catch (error) {
      console.error('Failed to fetch classes', error);
    }
  };

  const fetchTransactions = async (token: string, schoolId?: string) => {
    try {
      if (!schoolId) {
        setTransactions([]);
        return;
      }
      const url = `${API_URL}/transactions/school/${schoolId}`;
      const res = await fetch(url, { headers: { 'Authorization': `Bearer ${token}` } });
      if (res.ok) {
        const raw = await res.json();
        const mapped = raw.map((t: any) => ({
          id: t.id,
          schoolId: schoolId,
          type: t.type,
          category: t.category,
          incomeSource: t.category, // Fallback
          description: t.description,
          className: t.cashAccount?.class?.name || 'Unknown Class',
          classId: t.cashAccount?.class?.id,
          amount: Number(t.amount),
          recordedBy: t.creator?.name || 'System',
          paymentMethod: 'Tunai / Transfer',
          date: new Date(t.createdAt).toLocaleString('id-ID'),
        }));
        setTransactions(mapped);
      }
    } catch (error) {
      console.error('Failed to fetch transactions', error);
    }
  };

  const fetchBillings = async (token: string, schoolId?: string) => {
    try {
      if (!schoolId) {
        setBillings([]);
        return;
      }
      const url = `${API_URL}/billings/school/${schoolId}`;
      const res = await fetch(url, { headers: { 'Authorization': `Bearer ${token}` } });
      if (res.ok) {
        const raw = await res.json();
        const mapped = raw.map((b: any) => ({
          schoolId: schoolId,
          id: b.id,
          studentId: b.studentId,
          studentName: b.student?.name || 'Unknown Student',
          className: b.student?.enrollments?.[0]?.class?.name || 'Unknown Class',
          classId: b.student?.enrollments?.[0]?.class?.id,
          schemeTitle: b.duesScheme?.title || 'Iuran',
          period: new Date(b.duesScheme?.dueDate || b.dueDate).toLocaleString('id-ID', { month: 'long', year: 'numeric' }),
          amountDue: Number(b.amountDue),
          amountPaid: Number(b.amountPaid),
          status: b.status,
          paidAt: b.status === 'PAID' ? new Date().toLocaleString('id-ID') : '', // Approximation since DB doesn't track paidAt on billing
          paymentMethod: 'System',
        }));
        setBillings(mapped);
      }
    } catch (error) {
      console.error('Failed to fetch billings', error);
    }
  };

  useEffect(() => {
    // Check for stored session
    let sessionRole: string | undefined = undefined;
    const storedSession = localStorage.getItem('sistemkas_session');
    if (storedSession) {
      try {
        const parsed = JSON.parse(storedSession);
        setCurrentUser(parsed);
        sessionRole = parsed.role;
        if (parsed.schoolId && parsed.role !== 'SUPER_ADMIN') setActiveSchoolId(parsed.schoolId);
        if (parsed.name) setProfileName(parsed.name);
        
        // Auto fetch data
        fetchUsers(parsed.token, parsed.role !== 'SUPER_ADMIN' ? parsed.schoolId : undefined);
        fetchAcademicYears(parsed.token, parsed.role !== 'SUPER_ADMIN' ? parsed.schoolId : undefined);
        fetchClasses(parsed.token, undefined, parsed.role !== 'SUPER_ADMIN' ? parsed.schoolId : undefined);
        fetchTransactions(parsed.token, parsed.role !== 'SUPER_ADMIN' ? parsed.schoolId : undefined);
        fetchBillings(parsed.token, parsed.role !== 'SUPER_ADMIN' ? parsed.schoolId : undefined);
      } catch (err) {
        console.error('Invalid stored session');
        localStorage.removeItem('sistemkas_session');
      }
    }
    fetchSchools(sessionRole);
  }, []);

  // Authentication State
  const [loginEmail, setLoginEmail] = useState<string>('bendahara@sdn08pagi.sch.id');
  const [loginPassword, setLoginPassword] = useState<string>('Password123!');
  const [loginError, setLoginError] = useState<string>('');
  const [isLoggingIn, setIsLoggingIn] = useState<boolean>(false);

  // Login Screen Hero Media Settings (Admin Customizable)
  const [heroMediaUrl, setHeroMediaUrl] = useState<string>(
    'https://images.unsplash.com/photo-1580582932707-520aed937b7b?q=80&w=1600&auto=format&fit=crop'
  );
  const [heroMediaType, setHeroMediaType] = useState<'IMAGE' | 'VIDEO'>('IMAGE');
  const [showMediaModal, setShowMediaModal] = useState<boolean>(false);
  const [inputMediaUrl, setInputMediaUrl] = useState<string>(heroMediaUrl);
  const [inputMediaType, setInputMediaType] = useState<'IMAGE' | 'VIDEO'>('IMAGE');

  // Active Tab Views
  const [activeTab, setActiveTab] = useState<'OVERVIEW' | 'LEDGER' | 'STUDENTS' | 'REPORTS' | 'USERS' | 'CLASSES'>('OVERVIEW');
  const [portalTab, setPortalTab] = useState<'CURRENT' | 'HISTORY'>('CURRENT');
  const [reportPeriodFilter, setReportPeriodFilter] = useState<string>('Agustus 2026');
  const [reportStatusFilter, setReportStatusFilter] = useState<'ALL' | 'PAID' | 'PENDING'>('ALL');

  // Modals
  const [showTransactionModal, setShowTransactionModal] = useState<boolean>(false);
  const [showStudentModal, setShowStudentModal] = useState<boolean>(false);
  const [showUserModal, setShowUserModal] = useState<boolean>(false);
  const [showSchoolModal, setShowSchoolModal] = useState<boolean>(false);
  const [showSchoolListModal, setShowSchoolListModal] = useState<boolean>(false);
  const [editingSchoolId, setEditingSchoolId] = useState<string | null>(null);
  const [newSchoolName, setNewSchoolName] = useState<string>('');
  const [newSchoolCode, setNewSchoolCode] = useState<string>('');
  const [showScheduleModal, setShowScheduleModal] = useState<boolean>(false);
  const [showPayModal, setShowPayModal] = useState<boolean>(false);
  const [showProfileModal, setShowProfileModal] = useState<boolean>(false);
  const [showPdfEmailModal, setShowPdfEmailModal] = useState<boolean>(false);
  const [showStudentDetailModal, setShowStudentDetailModal] = useState<boolean>(false);

  // Selected Objects
  const [selectedBilling, setSelectedBilling] = useState<StudentBilling | null>(null);
  const [selectedStudentDetail, setSelectedStudentDetail] = useState<UserAccount | null>(null);
  const [pdfReportType, setPdfReportType] = useState<'LEDGER' | 'REPORTS'>('LEDGER');
  const [targetEmail, setTargetEmail] = useState<string>('');
  const [payMethod, setPayMethod] = useState<string>('Tunai / Cash');
  const [payAmountPaid, setPayAmountPaid] = useState<string>('');
  const [payExcessAction, setPayExcessAction] = useState<string>('Infaq');

  // Profile Change Password Form State
  const [profileName, setProfileName] = useState<string>('');
  const [oldPassword, setOldPassword] = useState<string>('');
  const [newPassword, setNewPassword] = useState<string>('');

  // Notification Toast
  const [toastMessage, setToastMessage] = useState<string>('');
  const [studentValidationError, setStudentValidationError] = useState<string>('');
  const [classValidationError, setClassValidationError] = useState<string>('');

  // Refetch data when activeSchoolId changes
  useEffect(() => {
    if (currentUser?.token && activeSchoolId) {
      // For ADMIN, they are restricted to their own school, for SUPER_ADMIN they can switch
      const fetchSchoolId = currentUser.role !== 'SUPER_ADMIN' ? currentUser.schoolId : activeSchoolId;
      fetchUsers(currentUser.token, fetchSchoolId);
      fetchAcademicYears(currentUser.token, fetchSchoolId);
      fetchClasses(currentUser.token, undefined, fetchSchoolId);
      fetchTransactions(currentUser.token, fetchSchoolId);
      fetchBillings(currentUser.token, fetchSchoolId);
    }
  }, [activeSchoolId, currentUser]);

  // Master Data Setup
  const [studentName, setStudentName] = useState<string>('');
  const [studentClass, setStudentClass] = useState<string>('');
  const [studentEmail, setStudentEmail] = useState<string>('');
  const [studentPassword, setStudentPassword] = useState<string>('Password123!');
  const [studentGender, setStudentGender] = useState<'MALE' | 'FEMALE'>('MALE');

  // User Creation Form State (Admin)
  const [newUserName, setNewUserName] = useState<string>('');
  const [newUserEmail, setNewUserEmail] = useState<string>('');
  const [newUserPassword, setNewUserPassword] = useState<string>('Password123!');
  const [newUserRole, setNewUserRole] = useState<'SUPER_ADMIN' | 'ADMIN' | 'TREASURER' | 'KORLAS' | 'PARENT'>('KORLAS');
  const [editingUserId, setEditingUserId] = useState<string | null>(null);
  const [selectedChildStudentId, setSelectedChildStudentId] = useState<string>('');
  const [selectedKorlasClass, setSelectedKorlasClass] = useState<string>('Kelas 5-A');

  // Cron Schedule Form State
  const [cronDay, setCronDay] = useState<number>(1);
  const [cronAmount, setCronAmount] = useState<number>(25000);
  const [cronTitle, setCronTitle] = useState<string>('Iuran Kas Wajib Bulanan');
  const [cronClass, setCronClass] = useState<string>('');

  // Transaction & Mutasi Form State
  const [txnType, setTxnType] = useState<'INCOME' | 'EXPENSE'>('INCOME');
  const [txnClass, setTxnClass] = useState<string>('');
  const [txnAmount, setTxnAmount] = useState<string>('');
  const [incomeSource, setIncomeSource] = useState<string>('Iuran Kas Siswa');
  const [txnDescription, setTxnDescription] = useState<string>('');

  // Registered User Accounts List (Loaded from API)
  const [userAccounts, setUserAccounts] = useState<UserAccount[]>([]);

  // Classes & Academic Years List (Loaded from API)
  const [classes, setClasses] = useState<any[]>([]);
  const korlasClassName = currentUser?.role === 'KORLAS' ? (classes.find((c: any) => c.id === currentUser.managedClass)?.name || currentUser.managedClass || 'Kelas Anda') : '';
  const [academicYears, setAcademicYears] = useState<any[]>([]);
  const [showClassModal, setShowClassModal] = useState<boolean>(false);
  const [showAcademicYearModal, setShowAcademicYearModal] = useState<boolean>(false);
  const [newClassName, setNewClassName] = useState<string>('');
  const [newClassAcademicYearId, setNewClassAcademicYearId] = useState<string>('');
  const [editingClassId, setEditingClassId] = useState<string | null>(null);
  const [editingAcademicYearId, setEditingAcademicYearId] = useState<string | null>(null);
  const [newAcademicYearName, setNewAcademicYearName] = useState<string>('');
  const [newAcademicYearIsCurrent, setNewAcademicYearIsCurrent] = useState<boolean>(true);

  // Transaction Ledger State
  const [transactions, setTransactions] = useState<any[]>([]);

  // Student Billing Dues State
  const [billings, setBillings] = useState<any[]>([]);

  // ----------------------------------------------------
  // STRICT MULTI-TENANCY DATA FILTERING FOR KORLAS
  // ----------------------------------------------------
  const scopedTransactions = transactions.filter((t) => {
    if (t.schoolId !== activeSchoolId) return false;
    if (currentUser?.role === 'KORLAS' && currentUser.managedClass) {
      return t.classId === currentUser.managedClass;
    }
    return true;
  });

  const scopedBillings = billings.filter((b) => {
    if (b.schoolId !== activeSchoolId) return false;
    if (currentUser?.role === 'KORLAS' && currentUser.managedClass) {
      return b.classId === currentUser.managedClass;
    }
    return true;
  });

  const scopedStudents = userAccounts.filter((u) => {
    if (u.schoolId !== activeSchoolId) return false;
    if (u.role !== 'STUDENT') return false;
    if (currentUser?.role === 'KORLAS' && currentUser.managedClass) {
      return u.enrollments?.[0]?.classId === currentUser.managedClass;
    }
    return true;
  });

  // Dynamic Live Database Calculations for Scoped View
  const totalIncome = scopedTransactions.filter((t) => t.type === 'INCOME').reduce((sum, t) => sum + t.amount, 0);
  const totalExpense = scopedTransactions.filter((t) => t.type === 'EXPENSE').reduce((sum, t) => sum + t.amount, 0);
  const computedBalance = totalIncome - totalExpense;

  const totalDuesExpected = scopedBillings.reduce((sum, b) => sum + b.amountDue, 0);
  const totalDuesCollected = scopedBillings.reduce((sum, b) => sum + b.amountPaid, 0);
  const totalPendingDues = totalDuesExpected - totalDuesCollected;
  const computedCollectionRate = totalDuesExpected > 0 ? ((totalDuesCollected / totalDuesExpected) * 100).toFixed(1) : '0';

  const formatIDR = (val: number) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val);
  };

  const showToast = (msg: string) => {
    setToastMessage(msg);
    setTimeout(() => setToastMessage(''), 4000);
  };

  const handleLogin = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoginError('');
    setIsLoggingIn(true);

    try {
      const res = await fetch(`${API_URL}/auth/login`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email: loginEmail, password: loginPassword }),
      });

      if (res.ok) {
        const data = await res.json();
        const session = {
          id: data.user.id,
          name: data.user.name,
          email: data.user.email,
          role: data.user.role,
          schoolId: data.user.schoolId,
          managedClass: data.user.managedClass,
          studentId: data.user.studentId,
          token: data.accessToken,
        };
        if (session.schoolId && session.role !== 'SUPER_ADMIN') {
          setActiveSchoolId(session.schoolId);
        } else if (session.role === 'SUPER_ADMIN') {
          setActiveSchoolId('');
        }
        
        setCurrentUser(session);
        setProfileName(session.name);
        
        // Save to localStorage for persistence
        localStorage.setItem('sistemkas_session', JSON.stringify(session));
        
        if (session.role === 'SUPER_ADMIN' || session.role === 'ADMIN') {
          fetchUsers(session.token, session.role !== 'SUPER_ADMIN' ? session.schoolId : undefined);
        }
      } else {
        const errData = await res.json();
        setLoginError(errData.message || 'Email atau password tidak valid.');
      }
    } catch (err) {
      console.error('Login error', err);
      setLoginError('Terjadi kesalahan jaringan.');
    }
    
    setIsLoggingIn(false);
  };

  const handleSaveSchool = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!newSchoolName || !newSchoolCode) return;
    
    try {
      if (editingSchoolId) {
        const res = await fetch(`${API_URL}/schools/${editingSchoolId}`, {
          method: 'PATCH',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ name: newSchoolName, code: newSchoolCode })
        });
        if (res.ok) {
          showToast(`✓ Sekolah '${newSchoolName}' berhasil diperbarui!`);
          fetchSchools();
        }
      } else {
        const res = await fetch(`${API_URL}/schools`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ name: newSchoolName, code: newSchoolCode })
        });
        if (res.ok) {
          showToast(`✓ Sekolah '${newSchoolName}' berhasil ditambahkan!`);
          fetchSchools();
        }
      }
      
      setShowSchoolModal(false);
      setEditingSchoolId(null);
      setNewSchoolName('');
      setNewSchoolCode('');
    } catch (error) {
      console.error(error);
      showToast('❌ Terjadi kesalahan jaringan.');
    }
  };

  const handleEditSchoolClick = (s: School) => {
    setEditingSchoolId(s.id);
    setNewSchoolName(s.name);
    setNewSchoolCode(s.code);
    setShowSchoolModal(true);
  };

  const handleDeleteSchool = async (id: string) => {
    if (confirm('Apakah Anda yakin ingin menghapus sekolah ini?')) {
      try {
        const res = await fetch(`${API_URL}/schools/${id}`, { method: 'DELETE' });
        if (res.ok) {
          showToast('✓ Sekolah berhasil dihapus.');
          if (activeSchoolId === id) setActiveSchoolId('');
          fetchSchools();
        }
      } catch (error) {
        console.error(error);
      }
    }
  };

  const handleLogout = () => {
    localStorage.removeItem('sistemkas_session');
    setCurrentUser(null);
    setProfileName('');
    setActiveTab('OVERVIEW');
  };

  // Admin Hero Media Save Handler
  const handleSaveHeroMedia = (e: React.FormEvent) => {
    e.preventDefault();
    setHeroMediaUrl(inputMediaUrl);
    setHeroMediaType(inputMediaType);
    setShowMediaModal(false);
    showToast('🖼️ Foto/Video Banner Login Sekolah berhasil diperbarui!');
  };

  // Local File Upload Handler for Login Banner
  const handleFileUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;

    const isVideo = file.type.startsWith('video/');
    setInputMediaType(isVideo ? 'VIDEO' : 'IMAGE');

    const reader = new FileReader();
    reader.onload = (event) => {
      if (event.target?.result) {
        setInputMediaUrl(event.target.result as string);
        showToast(`📁 File '${file.name}' berhasil dipilih dari komputer!`);
      }
    };
    reader.readAsDataURL(file);
  };

  // Profile & Password Change Handler
  const handleUpdateProfile = (e: React.FormEvent) => {
    e.preventDefault();
    if (!currentUser) return;

    setCurrentUser({ ...currentUser, name: profileName });
    setUserAccounts(userAccounts.map((u) => (u.id === currentUser.id ? { ...u, name: profileName } : u)));
    setShowProfileModal(false);
    setOldPassword('');
    setNewPassword('');
    showToast('✓ Profil dan password Anda berhasil diperbarui!');
  };

  // Direct Student Registration with Strict Korlas Class Validation
  const handleAddStudentDirect = async (e: React.FormEvent) => {
    e.preventDefault();
    setStudentValidationError('');
    if (!studentName || !activeSchoolId) return;

    if (currentUser?.role === 'KORLAS' && currentUser.managedClass) {
      if (studentClass !== currentUser.managedClass && studentClass !== '') {
        setStudentValidationError(`⚠️ Sebagai Korlas, Anda hanya diperbolehkan mendaftarkan siswa untuk kelas Anda!`);
        return;
      }
    }

    const targetClass = (currentUser?.role === 'KORLAS' && currentUser.managedClass) ? currentUser.managedClass : studentClass;
    if (!targetClass) {
      setStudentValidationError('⚠️ Silakan pilih kelas!');
      return;
    }

    try {
      const payload = {
        schoolId: activeSchoolId,
        name: studentName,
        email: studentEmail || `${studentName.toLowerCase().replace(/\s+/g, '')}@sdn08pagi.sch.id`,
        password: studentPassword,
        role: 'STUDENT',
        enrolledClassId: targetClass,
        gender: studentGender
      };

      const res = await fetch(`${API_URL}/auth/register`, {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${currentUser?.token}`
        },
        body: JSON.stringify(payload)
      });
      
      if (res.ok) {
        showToast(`✓ Siswa '${studentName}' berhasil ditambahkan!`);
        fetchUsers(currentUser!.token, activeSchoolId);
        setStudentName('');
        setStudentEmail('');
        setShowStudentModal(false);
      } else {
        const err = await res.json();
        setStudentValidationError(`❌ Gagal menambahkan siswa: ${err.message || 'Error'}`);
      }
    } catch (err) {
      setStudentValidationError('❌ Terjadi kesalahan jaringan.');
    }
  };

  // Admin Account Creation & Edit
  const handleCreateUser = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!newUserName || !newUserEmail || !currentUser?.token) return;

    try {
      const payload: any = {
        name: newUserName,
        email: newUserEmail,
        password: newUserPassword || 'Password123!',
        role: newUserRole === 'SUPER_ADMIN' ? 'SUPER_ADMIN' : (newUserRole === 'ADMIN' ? 'ADMIN' : newUserRole),
      };

      if (activeSchoolId) {
        payload.schoolId = activeSchoolId;
      }

      if (editingUserId) {
        const res = await fetch(`${API_URL}/users/${editingUserId}`, {
          method: 'PATCH',
          headers: { 
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${currentUser.token}`
          },
          body: JSON.stringify(payload)
        });
        if (res.ok) {
          showToast(`✓ Akun '${newUserName}' berhasil diperbarui!`);
        }
      } else {
        const payloadRegister: any = { ...payload };
        if (newUserRole === 'KORLAS') payloadRegister.managedClassId = selectedKorlasClass;
        if (newUserRole === 'PARENT') payloadRegister.studentId = selectedChildStudentId;
        
        const res = await fetch(`${API_URL}/auth/register`, {
          method: 'POST',
          headers: { 
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${currentUser.token}`
          },
          body: JSON.stringify(payloadRegister)
        });
        if (res.ok) {
          showToast(`✓ Akun '${newUserName}' berhasil dibuat!`);
        } else {
          const err = await res.json();
          showToast(`❌ Gagal membuat akun: ${err.message || 'Error'}`);
          return;
        }
      }

      // Refresh users
      fetchUsers(currentUser.token, currentUser.role !== 'SUPER_ADMIN' ? activeSchoolId : undefined);

      setNewUserName('');
      setNewUserEmail('');
      setNewUserPassword('');
      setSelectedChildStudentId('');
      setEditingUserId(null);
      setShowUserModal(false);
    } catch (error) {
      console.error(error);
      showToast('❌ Terjadi kesalahan jaringan.');
    }
  };

  const handleEditUserClick = (u: UserAccount) => {
    setEditingUserId(u.id);
    setNewUserName(u.name);
    setNewUserEmail(u.email);
    setNewUserRole(u.role as any);
    setSelectedKorlasClass(u.managedClass || 'Kelas 5-A');
    setSelectedChildStudentId(u.studentId || '');
    setShowUserModal(true);
  };

  const handleDeleteUserClick = async (id: string) => {
    if (confirm('Apakah Anda yakin ingin menghapus akun ini?')) {
      if (!currentUser?.token) return;
      try {
        const res = await fetch(`${API_URL}/users/${id}`, {
          method: 'DELETE',
          headers: { 'Authorization': `Bearer ${currentUser.token}` }
        });
        if (res.ok) {
          showToast('✓ Akun berhasil dihapus.');
          fetchUsers(currentUser.token, currentUser.role !== 'SUPER_ADMIN' ? activeSchoolId : undefined);
        } else {
          showToast('❌ Gagal menghapus akun.');
        }
      } catch (error) {
        showToast('❌ Terjadi kesalahan jaringan.');
      }
    }
  };

  // --- Academic Year Handlers ---
  const handleCreateAcademicYear = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!activeSchoolId) {
      showToast('❌ Silakan pilih sekolah aktif terlebih dahulu!');
      return;
    }
    if (!newAcademicYearName || !currentUser?.token) return;
    try {
      const payload = { schoolId: activeSchoolId, year: newAcademicYearName, isCurrent: newAcademicYearIsCurrent };
      const method = editingAcademicYearId ? 'PATCH' : 'POST';
      const url = editingAcademicYearId
        ? `${API_URL}/academic-years/${editingAcademicYearId}`
        : `${API_URL}/academic-years`;

      const res = await fetch(url, {
        method,
        headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${currentUser.token}` },
        body: JSON.stringify(payload)
      });
      if (res.ok) {
        showToast(`✓ Tahun Ajaran berhasil ${editingAcademicYearId ? 'diperbarui' : 'ditambahkan'}!`);
        fetchAcademicYears(currentUser.token, activeSchoolId);
        setNewAcademicYearName('');
        setEditingAcademicYearId(null);
        setShowAcademicYearModal(false);
      } else showToast(`❌ Gagal ${editingAcademicYearId ? 'memperbarui' : 'menambahkan'} Tahun Ajaran.`);
    } catch (error) { showToast('❌ Kesalahan jaringan.'); }
  };

  const handleEditAcademicYearClick = (ay: any) => {
    setEditingAcademicYearId(ay.id);
    setNewAcademicYearName(ay.year);
    setNewAcademicYearIsCurrent(ay.isCurrent);
    setShowAcademicYearModal(true);
  };

  const handleDeleteAcademicYear = async (id: string) => {
    if (confirm('Apakah Anda yakin ingin menghapus tahun ajaran ini? Data kelas yang terhubung mungkin akan ikut terhapus!')) {
      if (!currentUser?.token) return;
      try {
        const res = await fetch(`${API_URL}/academic-years/${id}`, {
          method: 'DELETE',
          headers: { 'Authorization': `Bearer ${currentUser.token}` }
        });
        if (res.ok) {
          showToast('✓ Tahun Ajaran berhasil dihapus.');
          fetchAcademicYears(currentUser.token, activeSchoolId);
        } else showToast('❌ Gagal menghapus Tahun Ajaran.');
      } catch (error) { showToast('❌ Kesalahan jaringan.'); }
    }
  };

  // --- Class Handlers ---
  const handleCreateClass = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!activeSchoolId) {
      showToast('❌ Silakan pilih sekolah aktif terlebih dahulu!');
      return;
    }
    if (!newClassName || !newClassAcademicYearId || !currentUser?.token) return;
    try {
      const payload = { name: newClassName, academicYearId: newClassAcademicYearId };
      const method = editingClassId ? 'PATCH' : 'POST';
      const url = editingClassId 
        ? `${API_URL}/classes/${editingClassId}` 
        : `${API_URL}/classes`;

      const res = await fetch(url, {
        method,
        headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${currentUser.token}` },
        body: JSON.stringify(payload)
      });
      if (res.ok) {
        showToast(`✓ Kelas berhasil ${editingClassId ? 'diperbarui' : 'ditambahkan'}!`);
        const fetchSchoolId = currentUser.role !== 'SUPER_ADMIN' ? currentUser.schoolId : activeSchoolId;
        fetchClasses(currentUser.token, undefined, fetchSchoolId);
        setNewClassName('');
        setEditingClassId(null);
        setShowClassModal(false);
      } else {
        const err = await res.json();
        showToast(`❌ Gagal ${editingClassId ? 'memperbarui' : 'menambahkan'} Kelas: ${err.message || 'Error'}`);
      }
    } catch (error) { showToast('❌ Kesalahan jaringan.'); }
  };

  const handleEditClassClick = (cls: any) => {
    setEditingClassId(cls.id);
    setNewClassName(cls.name);
    setNewClassAcademicYearId(cls.academicYearId);
    setShowClassModal(true);
  };

  const handleDeleteClass = async (id: string) => {
    if (confirm('Apakah Anda yakin ingin menghapus kelas ini?')) {
      if (!currentUser?.token) return;
      try {
        const res = await fetch(`${API_URL}/classes/${id}`, {
          method: 'DELETE',
          headers: { 'Authorization': `Bearer ${currentUser.token}` }
        });
        if (res.ok) {
          showToast('✓ Kelas berhasil dihapus.');
          const fetchSchoolId = currentUser.role !== 'SUPER_ADMIN' ? currentUser.schoolId : activeSchoolId;
          fetchClasses(currentUser.token, undefined, fetchSchoolId);
        } else showToast('❌ Gagal menghapus Kelas.');
      } catch (error) { showToast('❌ Kesalahan jaringan.'); }
    }
  };

  // Scheduled Dues Handler
  const handleSaveCronSchedule = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!currentUser?.token) return;

    const assignedClassId = currentUser?.role === 'KORLAS' && currentUser.managedClass ? currentUser.managedClass : cronClass;

    if (!assignedClassId) {
      showToast('❌ Silakan pilih kelas terlebih dahulu!');
      return;
    }

    try {
      // 1. Fetch Cash Account for the Class
      const cashRes = await fetch(`${API_URL}/cash-accounts/class/${assignedClassId}`, {
        headers: { 'Authorization': `Bearer ${currentUser.token}` }
      });
      if (!cashRes.ok) throw new Error('Gagal mengambil data akun kas');
      const cashAccounts = await cashRes.json();
      if (!cashAccounts || cashAccounts.length === 0) {
        showToast('❌ Akun Kas Utama untuk kelas ini belum ada.');
        return;
      }
      const cashAccountId = Array.isArray(cashAccounts) ? cashAccounts[0]?.id : cashAccounts?.id;

      // Calculate next due date based on cronDay
      const nextMonth = new Date();
      nextMonth.setMonth(nextMonth.getMonth() + 1);
      nextMonth.setDate(cronDay);
      const dueDate = nextMonth.toISOString();

      // 2. Post Dues Scheme
      const payload = {
        cashAccountId,
        title: cronTitle,
        amount: cronAmount,
        dueDate
      };

      const res = await fetch(`${API_URL}/billings/dues-scheme`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${currentUser.token}` },
        body: JSON.stringify(payload)
      });

      if (res.ok) {
        const result = await res.json();
        showToast(`⏰ Tagihan '${cronTitle}' berhasil dibuat untuk ${result.totalBillingsGenerated} siswa!`);
        fetchBillings(currentUser.token, currentUser.role !== 'SUPER_ADMIN' ? currentUser.schoolId : activeSchoolId);
        setShowScheduleModal(false);
      } else {
        const err = await res.json();
        showToast(`❌ Gagal membuat tagihan: ${err.message || 'Error'}`);
      }
    } catch (error) {
      console.error(error);
      showToast('❌ Terjadi kesalahan jaringan.');
    }
  };

  // Cash Income Mutation Handler
  const handleRecordTransaction = async (e: React.FormEvent) => {
    e.preventDefault();
    const numAmount = parseFloat(txnAmount);
    if (isNaN(numAmount) || numAmount <= 0) return;
    if (!currentUser?.token) return;

    const categoryText = txnType === 'INCOME' ? `Pemasukan (${incomeSource})` : 'Pengeluaran Kas';
    const assignedClassId = currentUser?.role === 'KORLAS' && currentUser.managedClass ? currentUser.managedClass : txnClass;

    if (!assignedClassId) {
      showToast('❌ Silakan pilih kelas terlebih dahulu!');
      return;
    }

    try {
      // 1. Fetch Cash Account for the Class
      const cashRes = await fetch(`${API_URL}/cash-accounts/class/${assignedClassId}`, {
        headers: { 'Authorization': `Bearer ${currentUser.token}` }
      });
      if (!cashRes.ok) throw new Error('Gagal mengambil data akun kas');
      const cashAccounts = await cashRes.json();
      if (!cashAccounts || cashAccounts.length === 0) {
        showToast('❌ Akun Kas Utama untuk kelas ini belum ada.');
        return;
      }
      const cashAccountId = cashAccounts[0].id;

      // 2. Post Transaction
      const payload = {
        cashAccountId,
        type: txnType,
        amount: numAmount,
        category: categoryText,
        description: txnDescription || (txnType === 'INCOME' ? `Setoran ${incomeSource}` : 'Pengeluaran Kas'),
        paymentMethod: payMethod
      };

      const res = await fetch(`${API_URL}/transactions`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${currentUser.token}` },
        body: JSON.stringify(payload)
      });

      if (res.ok) {
        showToast(`✓ Mutasi kas berhasil dicatat di buku besar!`);
        fetchTransactions(currentUser.token, currentUser.role !== 'SUPER_ADMIN' ? currentUser.schoolId : activeSchoolId);
        setTxnAmount('');
        setTxnDescription('');
        setShowTransactionModal(false);
      } else {
        const err = await res.json();
        showToast(`❌ Gagal mencatat transaksi: ${err.message || 'Error'}`);
      }
    } catch (error) {
      console.error(error);
      showToast('❌ Terjadi kesalahan jaringan.');
    }
  };

  // Payment Settlement Handler
  const handleSettlePayment = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedBilling || !currentUser?.token) return;

    const paidAmount = parseInt(payAmountPaid || '0');
    if (paidAmount <= 0) return;

    try {
      // 1. Fetch Cash Account for the Class
      const assignedClassId = selectedBilling.classId;
      if (!assignedClassId) {
        showToast('❌ Gagal mendapatkan ID Kelas siswa.');
        return;
      }
      const cashRes = await fetch(`${API_URL}/cash-accounts/class/${assignedClassId}`, {
        headers: { 'Authorization': `Bearer ${currentUser.token}` }
      });
      if (!cashRes.ok) throw new Error('Gagal mengambil data akun kas');
      const cashAccounts = await cashRes.json();
      if (!cashAccounts || cashAccounts.length === 0) {
        showToast('❌ Akun Kas Utama untuk kelas ini belum ada.');
        return;
      }
      const cashAccountId = cashAccounts[0].id;

      // 2. Post Transaction for Billing Payment
      const payload = {
        cashAccountId,
        billingId: selectedBilling.id,
        type: 'INCOME',
        amount: Math.min(paidAmount, selectedBilling.amountDue),
        category: 'Pemasukan (Iuran Kas Siswa)',
        description: `Pembayaran ${selectedBilling.schemeTitle} oleh ${selectedBilling.studentName}`,
        paymentMethod: payMethod
      };

      const res = await fetch(`${API_URL}/transactions`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${currentUser.token}` },
        body: JSON.stringify(payload)
      });

      if (!res.ok) {
        const err = await res.json();
        showToast(`❌ Gagal mencatat pembayaran: ${err.message || 'Error'}`);
        return;
      }

      // 3. Handle Excess Amount (Infaq/Donasi)
      if (paidAmount > selectedBilling.amountDue) {
        const excess = paidAmount - selectedBilling.amountDue;
        const excessPayload = {
          cashAccountId,
          type: 'INCOME',
          amount: excess,
          category: `Pemasukan (${payExcessAction})`,
          description: `${payExcessAction} dari ${selectedBilling.studentName} (${selectedBilling.className || 'Kelas 5-A'})`,
          paymentMethod: payMethod
        };
        await fetch(`${API_URL}/transactions`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${currentUser.token}` },
          body: JSON.stringify(excessPayload)
        });
      }

      showToast(`✓ Pembayaran tagihan berhasil dikonfirmasi!`);
      const fetchSchoolId = currentUser.role !== 'SUPER_ADMIN' ? currentUser.schoolId : activeSchoolId;
      fetchBillings(currentUser.token, fetchSchoolId);
      fetchTransactions(currentUser.token, fetchSchoolId);
      
      setShowPayModal(false);
      setSelectedBilling(null);
      setPayAmountPaid('');
    } catch (error) {
      console.error(error);
      showToast('❌ Terjadi kesalahan jaringan.');
    }
  };

  // PDF Export & Email Dialog Popup Handler
  const triggerPdfEmailDialog = (reportType: 'LEDGER' | 'REPORTS') => {
    setPdfReportType(reportType);
    setTargetEmail(currentUser?.email || 'admin@sdn08pagi.sch.id');
    setShowPdfEmailModal(true);
  };

  const handleExecutePdfAndSendEmail = (e: React.FormEvent) => {
    e.preventDefault();
    if (!currentUser?.token) return;
    setShowPdfEmailModal(false);

    const classObj = classes.find((c) => c.id === currentUser?.managedClass);
    const targetScopeClass = currentUser?.role === 'KORLAS' && currentUser.managedClass
      ? (classObj ? classObj.name : 'Kelas Korlas')
      : 'Semua Kelas';

    const doc = new jsPDF();
    doc.setFontSize(16);
    doc.text('SD NEGERI 08 PAGI', 14, 15);
    doc.setFontSize(10);
    doc.text(`Laporan Keuangan Kas Sekolah - ${targetScopeClass} (T.A. 2026/2027)`, 14, 22);
    doc.text(`Tanggal Cetak: ${new Date().toLocaleString('id-ID')}`, 14, 28);
    doc.text(`Penerima Email Arsip: ${targetEmail}`, 14, 34);
    doc.line(14, 38, 196, 38);

    if (pdfReportType === 'LEDGER') {
      doc.text(`BUKU BESAR MUTASI KAS (${targetScopeClass.toUpperCase()})`, 14, 45);
      autoTable(doc, {
        startY: 50,
        head: [['Tipe', 'Kategori / Sumber', 'Keterangan', 'Kelas', 'Metode', 'Pencatat', 'Waktu', 'Jumlah']],
        body: scopedTransactions.map((t) => [
          t.type,
          t.category,
          t.description,
          t.className || 'Kelas 5-A',
          t.paymentMethod,
          t.recordedBy,
          t.date,
          `Rp ${t.amount.toLocaleString('id-ID')}`,
        ]),
      });
    } else {
      doc.text(`LAPORAN KELUNASAN IURAN SISWA (${targetScopeClass.toUpperCase()})`, 14, 45);
      autoTable(doc, {
        startY: 50,
        head: [['Nama Siswa', 'Kelas', 'Periode', 'Tagihan', 'Status', 'Waktu Bayar']],
        body: filteredBillings.map((b) => [
          b.studentName,
          b.className || 'Kelas 5-A',
          b.period,
          `Rp ${b.amountDue.toLocaleString('id-ID')}`,
          b.status,
          b.paidAt || 'Belum ada setoran',
        ]),
      });
    }

    // Dispatch real email via NestJS Backend
    const pdfBase64 = doc.output('datauristring');
    fetch(`${API_URL}/notifications/email-report`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${currentUser.token}`
      },
      body: JSON.stringify({
        email: targetEmail,
        reportTitle: `Laporan Keuangan Kas - ${targetScopeClass}`,
        pdf: pdfBase64
      })
    }).then(async (res) => {
      if (res.ok) {
        showToast(`📄 PDF Laporan berhasil dikirimkan ke email ${targetEmail}!`);
      } else {
        const err = await res.json();
        showToast(`❌ Gagal mengirim email: ${err.message || 'Error'}`);
      }
    }).catch((err) => {
      console.error(err);
      showToast(`❌ Gagal mengirim email: Kesalahan jaringan`);
    });
  };

  // Filtered & Grouped Billings by Period & Status
  const filteredBillings = scopedBillings.filter((b) => {
    const periodMatch = b.period === reportPeriodFilter || reportPeriodFilter === 'ALL';
    if (!periodMatch) return false;
    if (reportStatusFilter === 'PAID') return b.status === 'PAID';
    if (reportStatusFilter === 'PENDING') return b.status === 'PENDING';
    return true;
  });

  // Current logged in Student / Parent child info
  const targetStudentProfile = userAccounts.find((u) => { if (u.schoolId !== activeSchoolId) return false;
    if (currentUser?.role === 'PARENT' && currentUser.studentId) {
      return u.id === currentUser.studentId;
    }
    return u.id === currentUser?.id;
  }) || {
    id: currentUser?.id || 'u-3',
    name: currentUser?.name || 'Andi Wijaya',
    email: currentUser?.email || 'andi@sdn08pagi.sch.id',
    role: 'STUDENT',
    className: currentUser?.className || 'Kelas 5-A',
    gender: 'MALE',
    status: 'ACTIVE',
  };

  const studentPersonalBillings = billings.filter((b) => {
    return b.studentId === targetStudentProfile.id;
  });

  const registeredStudents = userAccounts.filter((u) => u.role === 'STUDENT' && u.schoolId === activeSchoolId);

  // ----------------------------------------------------
  // RENDER: REDESIGNED 2-COLUMN LOGIN SCREEN (60% MEDIA SHOWCASE / 40% CREDENTIALS)
  // ----------------------------------------------------
  if (!currentUser) {
    return (
      <div className="min-h-screen bg-slate-900 flex items-center justify-center p-0 lg:p-6 font-sans">
        <div className="w-full max-w-7xl bg-white lg:rounded-3xl border border-slate-200 shadow-2xl overflow-hidden grid grid-cols-1 lg:grid-cols-12 min-h-[90vh]">

          {/* LEFT COLUMN: 60% Aesthetic School Media Showcase */}
          <div className="lg:col-span-7 relative bg-slate-950 flex flex-col justify-between p-8 lg:p-12 text-white overflow-hidden min-h-[400px]">

            {/* Background Media (Image or Video) */}
            {heroMediaType === 'VIDEO' ? (
              <video
                autoPlay
                loop
                muted
                playsInline
                className="absolute inset-0 w-full h-full object-cover opacity-60 scale-105"
                src={heroMediaUrl}
              />
            ) : (
              <img
                src={heroMediaUrl}
                alt={`${activeSchool?.name || 'Sekolah'} Showcase`}
                className="absolute inset-0 w-full h-full object-cover opacity-65 scale-105 transition-all duration-700 hover:scale-100"
              />
            )}

            {/* Dark Gradient Overlay */}
            <div className="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/40 to-indigo-950/30" />

            {/* Top Brand Header */}
            <div className="relative z-10 flex items-center space-x-3">
              <div className="h-12 w-12 rounded-2xl bg-indigo-600/90 backdrop-blur-md border border-white/20 flex items-center justify-center font-black text-2xl shadow-xl">
                🎒
              </div>
              <div>
                <span className="text-xs font-bold uppercase tracking-widest text-indigo-300">Portal Keuangan</span>
                <h2 className="text-xl font-black text-white">Multi-School Kas Terpadu</h2>
              </div>
            </div>

            {/* Middle Welcome Hero Content */}
            <div className="relative z-10 space-y-4 my-auto py-12">
              <span className="inline-block px-4 py-1.5 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 backdrop-blur-md">
                ✨ Transparan, Akuntabel & Terpercaya
              </span>
              <h1 className="text-3xl lg:text-4xl font-black leading-tight text-white">
                Sistem Keuangan Kas Sekolah & Portal Siswa Cerdas 📚
              </h1>
              <p className="text-sm text-slate-300 max-w-lg leading-relaxed font-medium">
                Solusi pencatatan iuran kas, mutasi terkategori, penjadwalan otomatis per kelas, dan notifikasi email bukti setoran secara real-time.
              </p>
            </div>

            {/* Bottom Feature Badges */}
            <div className="relative z-10 grid grid-cols-3 gap-3 pt-6 border-t border-white/10 text-xs">
              <div className="bg-white/10 backdrop-blur-md p-3.5 rounded-2xl border border-white/10">
                <span className="text-lg">📌</span>
                <p className="font-bold text-white mt-1">Multi-Tenancy Korlas</p>
                <p className="text-[10px] text-slate-300">Isolasi data per kelas</p>
              </div>
              <div className="bg-white/10 backdrop-blur-md p-3.5 rounded-2xl border border-white/10">
                <span className="text-lg">⏰</span>
                <p className="font-bold text-white mt-1">Cron Scheduler</p>
                <p className="text-[10px] text-slate-300">Iuran bulanan otomatis</p>
              </div>
              <div className="bg-white/10 backdrop-blur-md p-3.5 rounded-2xl border border-white/10">
                <span className="text-lg">📄</span>
                <p className="font-bold text-white mt-1">Export PDF & Email</p>
                <p className="text-[10px] text-slate-300">Laporan instan</p>
              </div>
            </div>
          </div>

          {/* RIGHT COLUMN: 40% Login Form & Credentials */}
          <div className="lg:col-span-5 p-8 lg:p-12 flex flex-col justify-center space-y-6 bg-white">
            <div className="space-y-2">
              <h2 className="text-2xl font-black text-slate-900">Selamat Datang 👋</h2>
              <p className="text-xs text-slate-500 font-medium">Silakan masuk menggunakan kredensial akun Anda</p>
            </div>

            {loginError && (
              <div className="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-600 text-xs font-bold">
                ⚠️ {loginError}
              </div>
            )}

            <form onSubmit={handleLogin} className="space-y-4 text-xs">
              <div>
                <label className="block text-slate-700 font-bold mb-1.5">Email Pengguna</label>
                <input
                  type="email"
                  required
                  value={loginEmail}
                  onChange={(e) => setLoginEmail(e.target.value)}
                  className="w-full bg-slate-50 border border-slate-300 rounded-2xl p-4 text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm font-medium"
                />
              </div>

              <div>
                <label className="block text-slate-700 font-bold mb-1.5">Password</label>
                <input
                  type="password"
                  required
                  value={loginPassword}
                  onChange={(e) => setLoginPassword(e.target.value)}
                  className="w-full bg-slate-50 border border-slate-300 rounded-2xl p-4 text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm font-medium"
                />
              </div>

              <button
                type="submit"
                disabled={isLoggingIn}
                className="w-full py-4 rounded-2xl gradient-button-school font-bold text-sm shadow-md transition-all hover:scale-[1.01] disabled:opacity-50"
              >
                {isLoggingIn ? 'Memverifikasi...' : '🚀 Masuk ke Sistem Kas'}
              </button>
            </form>

            {/* Quick Demo Selector */}
            <div className="pt-6 border-t border-slate-100 text-xs space-y-3">
              <p className="text-slate-400 font-bold text-center">Pilih Akun Demo Quick Login:</p>
              <div className="grid grid-cols-2 gap-2">
                <button
                  onClick={() => { setLoginEmail('sysadmin@sistemkas.com'); setLoginPassword('Password123!'); }}
                  className="col-span-2 p-2.5 rounded-xl bg-slate-800 text-white font-bold text-[11px] text-center shadow-lg"
                >
                  👑 0. System Admin
                </button>
                <button
                  onClick={() => { setLoginEmail('admin@sdn08pagi.sch.id'); setLoginPassword('Password123!'); }}
                  className="p-2.5 rounded-xl badge-school-indigo font-bold text-[11px] text-center"
                >
                  👑 1. Admin Sekolah
                </button>
                <button
                  onClick={() => { setLoginEmail('bendahara@sdn08pagi.sch.id'); setLoginPassword('Password123!'); }}
                  className="p-2.5 rounded-xl badge-school-emerald font-bold text-[11px] text-center"
                >
                  💼 2. Bendahara
                </button>
                <button
                  onClick={() => { setLoginEmail('korlas.5a@sdn08pagi.sch.id'); setLoginPassword('Password123!'); }}
                  className="p-2.5 rounded-xl badge-school-purple font-bold text-[11px] text-center"
                >
                  📌 3. Korlas (Kelas 5-A)
                </button>
                <button
                  onClick={() => { setLoginEmail('andi@sdn08pagi.sch.id'); setLoginPassword('Password123!'); }}
                  className="p-2.5 rounded-xl badge-school-amber font-bold text-[11px] text-center"
                >
                  🎓 4. Akun Siswa (Andi)
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    );
  }

  // ----------------------------------------------------
  // RENDER VIEW A: PORTAL SISWA & ORANG TUA
  // ----------------------------------------------------
  if (currentUser.role === 'STUDENT' || currentUser.role === 'PARENT') {
    return (
      <div className="min-h-screen bg-slate-50 text-slate-900 flex flex-col justify-between">
        <div>
          {/* Toast Notification */}
          {toastMessage && (
            <div className="fixed bottom-6 right-6 z-50 bg-slate-900 text-white px-5 py-3 rounded-2xl shadow-2xl text-xs font-bold border border-slate-700 animate-bounce">
              📬 {toastMessage}
            </div>
          )}

          <header className="light-panel sticky top-0 z-40 px-6 py-2.5 flex items-center justify-between shadow-xs">
            <div className="flex items-center space-x-2.5">
              <div className="h-8 w-8 rounded-xl bg-gradient-to-tr from-emerald-600 to-indigo-600 flex items-center justify-center font-black text-white text-base shadow-sm">
                🎒
              </div>
              <div>
                <h1 className="text-sm font-bold text-slate-900 leading-tight">Portal Siswa & Orang Tua</h1>
                <p className="text-[11px] text-slate-500 font-medium leading-tight">{activeSchool?.name || 'Sekolah'} • {targetStudentProfile.className || 'Kelas 5-A'}</p>
              </div>
            </div>

            <div className="flex items-center space-x-2.5">
              <button
                onClick={() => setShowProfileModal(true)}
                className="text-right hover:bg-slate-100 px-2.5 py-1 rounded-xl transition-all border border-slate-200 bg-white shadow-xs"
              >
                <p className="text-[11px] font-bold text-slate-900 leading-tight">{currentUser.name} ⚙️</p>
                <span className="px-2 py-0.2 rounded-md text-[9px] font-bold bg-emerald-100 text-emerald-800">
                  {currentUser.role === 'STUDENT' ? '🎓 Akun Siswa' : '👨‍👦 Akun Orang Tua'}
                </span>
              </button>
              <button onClick={handleLogout} className="text-[11px] text-rose-600 font-bold px-3 py-1 bg-rose-50 hover:bg-rose-100 rounded-xl transition-colors">
                Keluar
              </button>
            </div>
          </header>

          <main className="w-full px-6 lg:px-10 pt-8 space-y-6">
            {/* 2-Column Split Layout */}
            <div className="grid grid-cols-1 lg:grid-cols-12 gap-8">

              {/* LEFT COLUMN: Detailed Student Profile Card */}
              <div className="lg:col-span-4 space-y-6">
                <div className="light-card rounded-3xl p-6 bg-white border border-slate-200 shadow-md space-y-6">
                  <div className="text-center space-y-3 pb-6 border-b border-slate-100">
                    <div className="h-22 w-22 mx-auto rounded-full bg-gradient-to-tr from-emerald-500 via-indigo-600 to-purple-600 flex items-center justify-center text-white text-3xl font-black shadow-xl animate-float">
                      🎓
                    </div>
                    <div>
                      <h2 className="text-lg font-extrabold text-slate-900">{targetStudentProfile.name}</h2>
                      <p className="text-xs font-bold text-indigo-700 bg-indigo-50 px-3 py-1 rounded-full inline-block mt-1 border border-indigo-100">
                        {targetStudentProfile.className || 'Kelas 5-A'}
                      </p>
                    </div>
                  </div>

                  <div className="space-y-4 text-xs">
                    <h3 className="text-xs font-bold uppercase text-slate-400 tracking-wider">Informasi Lengkap Siswa</h3>

                    <div className="flex justify-between items-center py-2 border-b border-slate-50">
                      <span className="text-slate-500 font-medium">Jenis Kelamin:</span>
                      <span className="font-semibold text-slate-900">{targetStudentProfile.gender === 'MALE' ? 'Laki-laki (L)' : 'Perempuan (P)'}</span>
                    </div>

                    <div className="flex justify-between items-center py-2 border-b border-slate-50">
                      <span className="text-slate-500 font-medium">Email Akun:</span>
                      <span className="font-mono text-slate-800 text-[11px]">{targetStudentProfile.email}</span>
                    </div>

                    <div className="flex justify-between items-center py-2">
                      <span className="text-slate-500 font-medium">Status Akun:</span>
                      <span className="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">✓ AKTIF</span>
                    </div>
                  </div>

                  <button
                    onClick={() => setShowProfileModal(true)}
                    className="w-full py-3 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs transition-colors"
                  >
                    ⚙️ Ubah Profil & Password Saya
                  </button>
                </div>
              </div>

              {/* RIGHT COLUMN: Interactive Tabbed Dues & History Dashboard */}
              <div className="lg:col-span-8 space-y-6">

                {/* Tab Controls */}
                <div className="flex space-x-3 border-b border-slate-200 pb-3">
                  <button
                    onClick={() => setPortalTab('CURRENT')}
                    className={`px-5 py-3 rounded-2xl text-xs font-bold transition-all ${portalTab === 'CURRENT' ? 'bg-emerald-600 text-white shadow-md' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'}`}
                  >
                    💳 Tagihan Saat Ini & Pending ({studentPersonalBillings.filter(b => b.status === 'PENDING').length})
                  </button>
                  <button
                    onClick={() => setPortalTab('HISTORY')}
                    className={`px-5 py-3 rounded-2xl text-xs font-bold transition-all ${portalTab === 'HISTORY' ? 'bg-indigo-600 text-white shadow-md' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'}`}
                  >
                    📜 History Transaksi & Setoran ({studentPersonalBillings.length})
                  </button>
                </div>

                {/* TAB 1: TAGIHAN SAAT INI */}
                {portalTab === 'CURRENT' && (
                  <div className="light-card rounded-3xl p-6 bg-white border border-slate-200 shadow-md space-y-4">
                    <h3 className="text-base font-bold text-slate-900">Tagihan Iuran Aktif Siswa 📚</h3>

                    <div className="space-y-3">
                      {studentPersonalBillings.map((b) => (
                        <div key={b.id} className="p-5 rounded-2xl border border-slate-200 bg-slate-50/50 flex flex-wrap items-center justify-between gap-4 hover:border-indigo-300 transition-all shadow-xs">
                          <div>
                            <span className="text-[10px] font-extrabold uppercase text-indigo-700 bg-indigo-100/80 px-3 py-1 rounded-full">
                              {b.schemeTitle} • {b.period}
                            </span>
                            <h4 className="text-xl font-black text-slate-900 mt-2">{formatIDR(b.amountDue)}</h4>
                            {b.paidAt ? (
                              <p className="text-xs font-bold text-emerald-600 mt-1">🕒 Setor pada: {b.paidAt} ({b.paymentMethod})</p>
                            ) : (
                              <p className="text-xs text-amber-700 font-semibold mt-1">⏳ Silakan lakukan setoran ke Korlas / Bendahara</p>
                            )}
                          </div>

                          <div>
                            <span className={`px-4 py-2 rounded-2xl text-xs font-black shadow-xs ${b.status === 'PAID' ? 'bg-emerald-600 text-white' : 'bg-amber-500 text-white'}`}>
                              {b.status === 'PAID' ? '✓ LUNAS' : '⏳ BELUM LUNAS'}
                            </span>
                          </div>
                        </div>
                      ))}
                    </div>
                  </div>
                )}

                {/* TAB 2: HISTORY TRANSAKSI */}
                {portalTab === 'HISTORY' && (
                  <div className="light-card rounded-3xl p-6 bg-white border border-slate-200 shadow-md space-y-4">
                    <h3 className="text-base font-bold text-slate-900">Rekapitulasi History Setoran Pembayaran 📊</h3>
                    <div className="overflow-x-auto">
                      <table className="w-full text-left text-xs text-slate-700">
                        <thead className="bg-slate-100 uppercase text-[10px] text-slate-500">
                          <tr>
                            <th className="p-3">Program Iuran</th>
                            <th className="p-3">Periode</th>
                            <th className="p-3 text-right">Nominal</th>
                            <th className="p-3 text-center">Status</th>
                            <th className="p-3">Metode Bayar</th>
                            <th className="p-3">Waktu Bayar</th>
                          </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                          {studentPersonalBillings.map((b) => (
                            <tr key={b.id}>
                              <td className="p-3 font-bold text-slate-900">{b.schemeTitle}</td>
                              <td className="p-3 font-semibold text-indigo-700">{b.period}</td>
                              <td className="p-3 text-right font-black">{formatIDR(b.amountDue)}</td>
                              <td className="p-3 text-center">
                                <span className={`px-2.5 py-0.5 rounded-full text-[10px] font-bold ${b.status === 'PAID' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'}`}>
                                  {b.status === 'PAID' ? 'LUNAS' : 'PENDING'}
                                </span>
                              </td>
                              <td className="p-3 text-slate-600">{b.paymentMethod || '-'}</td>
                              <td className="p-3 text-slate-500">{b.paidAt || 'Belum ada setoran'}</td>
                            </tr>
                          ))}
                        </tbody>
                      </table>
                    </div>
                  </div>
                )}
              </div>
            </div>
          </main>
        </div>

        {/* Compact Slim Application Footer with Social Links */}
        <footer className="mt-6 py-3 border-t border-slate-200 text-center text-[11px] text-slate-500 bg-white shadow-xs">
          <div className="w-full px-6 flex flex-col sm:flex-row items-center justify-between gap-2">
            <p className="font-semibold text-slate-700">
              🏫 {activeSchool?.name || 'Sekolah'} • Sistem Informasi Keuangan Kas Sekolah
            </p>

            {/* Social Media Contact Links */}
            <div className="flex flex-wrap items-center justify-center gap-4 font-medium text-slate-600">
              <a href="https://facebook.com" target="_blank" rel="noreferrer" className="hover:text-indigo-600 transition-colors flex items-center gap-1">
                📘 Facebook
              </a>
              <a href="https://twitter.com" target="_blank" rel="noreferrer" className="hover:text-sky-500 transition-colors flex items-center gap-1">
                🐦 Twitter/X
              </a>
              <a href="https://instagram.com" target="_blank" rel="noreferrer" className="hover:text-pink-600 transition-colors flex items-center gap-1">
                📸 Instagram
              </a>
              <a href="https://wa.me/6281234567890" target="_blank" rel="noreferrer" className="hover:text-emerald-600 font-bold transition-colors flex items-center gap-1">
                💬 WhatsApp (Hubungi Kami)
              </a>
            </div>

            <p className="text-[10px] text-slate-400">
              Developed by Uwais Syahdan Riano • v1.2.0
            </p>
          </div>
        </footer>

        {/* Profile Modal */}
        {showProfileModal && (
          <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
            <div className="bg-white w-full max-w-lg p-6 rounded-3xl border border-slate-200 shadow-2xl space-y-5 max-h-[90vh] overflow-y-auto">
              <div className="flex justify-between items-center pb-3 border-b border-slate-100">
                <h3 className="text-base font-bold text-slate-900">Profil Saya & Ubah Password ⚙️</h3>
                <button onClick={() => setShowProfileModal(false)} className="text-slate-400 text-lg">✕</button>
              </div>

              <div className="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-xs space-y-2">
                <h4 className="font-bold text-slate-900 mb-2">Informasi Siswa Bersangkutan</h4>
                <div className="grid grid-cols-2 gap-2 text-slate-700">
                  <div><span className="text-slate-400">Kelas:</span> <strong className="text-indigo-600">{targetStudentProfile.className || 'Kelas 5-A'}</strong></div>
                  <div><span className="text-slate-400">Jenis Kelamin:</span> <strong>{targetStudentProfile.gender === 'MALE' ? 'Laki-laki' : 'Perempuan'}</strong></div>
                  <div><span className="text-slate-400">Email:</span> <strong>{targetStudentProfile.email}</strong></div>
                </div>
              </div>

              <form onSubmit={handleUpdateProfile} className="space-y-4 text-xs">
                <div>
                  <label className="block text-slate-700 font-bold mb-1">Nama Lengkap Pengguna</label>
                  <input type="text" required value={profileName} onChange={(e) => setProfileName(e.target.value)} className="w-full bg-slate-50 border border-slate-300 rounded-2xl p-3 text-slate-900 text-sm" />
                </div>
                <div>
                  <label className="block text-slate-700 font-bold mb-1">Password Saat Ini</label>
                  <input type="password" placeholder="Password Saat Ini" value={oldPassword} onChange={(e) => setOldPassword(e.target.value)} className="w-full bg-slate-50 border border-slate-300 rounded-2xl p-3 text-slate-900 text-sm" />
                </div>
                <div>
                  <label className="block text-slate-700 font-bold mb-1">Password Baru</label>
                  <input type="password" placeholder="Password Baru" value={newPassword} onChange={(e) => setNewPassword(e.target.value)} className="w-full bg-slate-50 border border-slate-300 rounded-2xl p-3 text-slate-900 text-sm" />
                </div>

                <div className="flex space-x-3 pt-2">
                  <button type="button" onClick={() => setShowProfileModal(false)} className="flex-1 py-3 rounded-2xl bg-slate-100 text-slate-700 font-bold">Batal</button>
                  <button type="submit" className="flex-1 py-3 rounded-2xl gradient-button-school text-white font-bold shadow-md">Simpan Perubahan</button>
                </div>
              </form>
            </div>
          </div>
        )}
      </div>
    );
  }

  // ----------------------------------------------------
  // RENDER VIEW B: EXECUTIVE ADMIN, BENDAHARA & KORLAS
  // ----------------------------------------------------
  return (
    <div className="min-h-screen bg-slate-50 text-slate-900 flex flex-col justify-between">
      <div>
        {/* Toast Notification */}
        {toastMessage && (
          <div className="fixed bottom-6 right-6 z-50 bg-slate-900 text-white px-5 py-3 rounded-2xl shadow-2xl text-xs font-bold border border-slate-700 animate-bounce">
            📬 {toastMessage}
          </div>
        )}

        {/* Light Top Navbar */}
        <header className="light-panel sticky top-0 z-40 px-6 py-2.5 flex items-center justify-between shadow-xs">
          <div className="flex items-center space-x-2.5">
            <div className="h-8 w-8 rounded-xl bg-gradient-to-tr from-indigo-600 to-emerald-500 flex items-center justify-center font-black text-white text-base shadow-sm">
              🎒
            </div>
            <div>
              <h1 className="text-sm font-bold gradient-text-school leading-tight">{activeSchool?.name || 'Sekolah'}</h1>
              <p className="text-[11px] text-slate-500 font-medium leading-tight">
                Keuangan Kas Sekolah • {currentUser.role === 'KORLAS' ? `Korlas (${korlasClassName})` : 'Multi-Kelas Master'}
              </p>
            </div>
            {currentUser?.role === 'SUPER_ADMIN' && (
              <div className="ml-4 flex items-center space-x-2">
                <div className="flex items-center bg-indigo-50 border border-indigo-200 rounded-xl px-3 py-1.5 shadow-sm">
                  <span className="text-xs font-bold text-indigo-800 mr-2">Sekolah:</span>
                  <select value={activeSchoolId} onChange={(e) => setActiveSchoolId(e.target.value)} className="bg-transparent text-indigo-900 text-sm font-bold focus:outline-none cursor-pointer">
                    <option value="">-- Pilih Sekolah --</option>
                    {schools.map(s => (
                      <option key={s.id} value={s.id}>{s.name}</option>
                    ))}
                  </select>
                </div>
                <button onClick={() => setShowSchoolListModal(true)} className="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded-xl text-[11px] font-bold shadow-sm transition-colors">
                  Manajemen Sekolah
                </button>
              </div>
            )}
          </div>

          <div className="flex items-center space-x-2.5">
            <div className="flex items-center space-x-2 bg-white px-3 py-1 rounded-xl border border-slate-200 shadow-xs">
              <button
                onClick={() => setShowProfileModal(true)}
                className="text-right hover:bg-slate-50 p-0.5 transition-colors"
              >
                <p className="text-[11px] font-bold text-slate-900 leading-tight">{currentUser.name} ⚙️</p>
                <span className="inline-block px-2 py-0.2 rounded-md text-[9px] font-bold badge-school-purple">
                  {formatRole(currentUser.role)} {currentUser.managedClass ? `(${korlasClassName})` : ''}
                </span>
              </button>
              <button onClick={handleLogout} className="text-[11px] text-rose-600 hover:text-rose-700 font-bold px-2.5 py-1 bg-rose-50 hover:bg-rose-100 rounded-xl transition-colors">
                Keluar
              </button>
            </div>
          </div>
        </header>

        {/* Main Container */}
        <main className="w-full px-6 lg:px-10 pt-8 space-y-8">
          {/* Banner Section */}
          <div className="light-card rounded-3xl p-8 relative overflow-hidden bg-white border border-slate-200 shadow-md">
            <div className="flex flex-col md:flex-row md:items-center justify-between gap-6 relative z-10">
              <div>
                <span className="text-xs font-bold tracking-wider text-purple-700 uppercase badge-school-purple px-3 py-1 rounded-full">
                  🏫 {activeSchool?.name || 'Sekolah'} • {currentUser.role === 'KORLAS' ? `Cakupan Kas ${korlasClassName}` : 'Semua Kelas'}
                </span>
                <h2 className="text-2xl font-black text-slate-900 mt-2">
                  {currentUser.role === 'KORLAS' ? `Dashboard Korlas (${korlasClassName}) 📌` : 'Manajemen Keuangan Kas & Multi-Tenancy 🎓'}
                </h2>
                <p className="text-xs text-slate-500 mt-1 max-w-xl font-medium">
                  {currentUser.role === 'KORLAS'
                    ? `Ringkasan kas, mutasi, dan tagihan diisolasi secara ketat khusus untuk ${korlasClassName}.`
                    : 'Akses penuh administrator untuk melihat dan mengelola keuangan seluruh kelas.'}
                </p>
              </div>

              <div className="flex flex-wrap gap-2">
                {currentUser.role === 'SUPER_ADMIN' && (
                  <button onClick={() => setShowMediaModal(true)} className="bg-purple-600 hover:bg-purple-700 text-white px-4 py-3 rounded-2xl font-bold text-xs shadow-md transition-all">
                    🖼️ Ganti Foto/Video Header Login
                  </button>
                )}

                {currentUser.role !== 'SUPER_ADMIN' && currentUser.role !== 'ADMIN' && (
                  <>
                    <button onClick={() => setShowScheduleModal(true)} className="bg-amber-600 hover:bg-amber-700 text-white px-4 py-3 rounded-2xl font-bold text-xs shadow-md transition-all">
                      📅 Penjadwalan Iuran ({currentUser.role === 'KORLAS' ? korlasClassName : 'Semua'})
                    </button>

                    <button onClick={() => setShowTransactionModal(true)} className="gradient-button-school px-4 py-3 rounded-2xl font-bold text-xs shadow-md transition-all">
                      + Catat Mutasi Kas ({currentUser.role === 'KORLAS' ? korlasClassName : 'Umum'})
                    </button>
                  </>
                )}
              </div>
            </div>
          </div>

          {/* Navigation Tabs */}
          <div className="flex space-x-2 border-b border-slate-200 pb-3">
            <button onClick={() => setActiveTab('OVERVIEW')} className={`px-4 py-2.5 rounded-2xl text-xs font-bold transition-all ${activeTab === 'OVERVIEW' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-600 hover:bg-slate-100'}`}>
              📈 Ringkasan Kas
            </button>
            <button onClick={() => setActiveTab('LEDGER')} className={`px-4 py-2.5 rounded-2xl text-xs font-bold transition-all ${activeTab === 'LEDGER' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-600 hover:bg-slate-100'}`}>
              📊 Buku Besar Mutasi ({scopedTransactions.length})
            </button>
            <button onClick={() => setActiveTab('STUDENTS')} className={`px-4 py-2.5 rounded-2xl text-xs font-bold transition-all ${activeTab === 'STUDENTS' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-600 hover:bg-slate-100'}`}>
              🎓 Data Siswa ({scopedStudents.length})
            </button>
            <button onClick={() => setActiveTab('REPORTS')} className={`px-4 py-2.5 rounded-2xl text-xs font-bold transition-all ${activeTab === 'REPORTS' ? 'bg-emerald-600 text-white shadow-md' : 'text-slate-600 hover:bg-slate-100'}`}>
              📋 Menu Laporan Iuran Siswa
            </button>
            {(currentUser.role === 'ADMIN' || currentUser.role === 'SUPER_ADMIN') && (
              <>
                <button onClick={() => setActiveTab('USERS')} className={`px-4 py-2.5 rounded-2xl text-xs font-bold transition-all ${activeTab === 'USERS' ? 'bg-blue-600 text-white shadow-md' : 'text-slate-600 hover:bg-slate-100'}`}>
                  🧑‍💻 Account Management ({userAccounts.filter(u => u.schoolId === activeSchoolId).length})
                </button>
                <button onClick={() => setActiveTab('CLASSES')} className={`px-4 py-2.5 rounded-2xl text-xs font-bold transition-all ${activeTab === 'CLASSES' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-600 hover:bg-slate-100'}`}>
                  🏫 Data Kelas
                </button>
              </>
            )}
          </div>

          {/* TAB 1: OVERVIEW */}
          {activeTab === 'OVERVIEW' && (
            <div className="space-y-6">
              <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div className="light-card p-6 rounded-3xl">
                  <p className="text-xs font-bold text-slate-500 uppercase">Saldo Kas ({currentUser.role === 'KORLAS' ? korlasClassName : 'Utama'})</p>
                  <h3 className="text-2xl font-black mt-2 text-slate-900">{formatIDR(computedBalance)}</h3>
                </div>
                <div className="light-card p-6 rounded-3xl">
                  <p className="text-xs font-bold text-slate-500 uppercase">Capaian Pembayaran</p>
                  <h3 className="text-2xl font-black mt-2 text-indigo-600">{computedCollectionRate}%</h3>
                </div>
                <div className="light-card p-6 rounded-3xl">
                  <p className="text-xs font-bold text-slate-500 uppercase">Total Terkumpul</p>
                  <h3 className="text-2xl font-black mt-2 text-emerald-600">{formatIDR(totalDuesCollected)}</h3>
                </div>
                <div className="light-card p-6 rounded-3xl">
                  <p className="text-xs font-bold text-slate-500 uppercase">Tunggakan Pending</p>
                  <h3 className="text-2xl font-black mt-2 text-amber-600">{formatIDR(totalPendingDues)}</h3>
                </div>
              </div>

              <div className="light-card rounded-3xl p-6 space-y-4">
                <h3 className="text-base font-bold text-slate-900">Mutasi Kas Terkategori Terbaru ({currentUser.role === 'KORLAS' ? korlasClassName : 'Semua Kelas'})</h3>
                <div className="overflow-x-auto">
                  <table className="w-full text-left text-xs text-slate-700">
                    <thead className="bg-slate-100 uppercase text-[10px] text-slate-500">
                      <tr>
                        <th className="p-3">Tipe</th>
                        <th className="p-3">Kategori / Sumber Asal</th>
                        <th className="p-3">Keterangan</th>
                        <th className="p-3">Kelas</th>
                        <th className="p-3">Waktu</th>
                        <th className="p-3 text-right">Jumlah</th>
                      </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100">
                      {scopedTransactions.map((tx) => (
                        <tr key={tx.id}>
                          <td className="p-3">
                            <span className={`px-2.5 py-0.5 rounded-full text-[10px] font-bold ${tx.type === 'INCOME' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'}`}>
                              {tx.type}
                            </span>
                          </td>
                          <td className="p-3 font-bold text-slate-900">{tx.category}</td>
                          <td className="p-3 text-slate-600">{tx.description}</td>
                          <td className="p-3 font-bold text-indigo-600">{tx.className || 'Kelas 5-A'}</td>
                          <td className="p-3 text-slate-500">{tx.date}</td>
                          <td className={`p-3 text-right font-black ${tx.type === 'INCOME' ? 'text-emerald-600' : 'text-rose-600'}`}>
                            {tx.type === 'INCOME' ? '+' : '-'}{formatIDR(tx.amount)}
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          )}

          {/* TAB 2: LEDGER */}
          {activeTab === 'LEDGER' && (
            <div className="light-card rounded-3xl p-6 space-y-4">
              <div className="flex justify-between items-center pb-3 border-b border-slate-100">
                <h3 className="text-lg font-bold text-slate-900">Buku Besar Mutasi Kas ({currentUser.role === 'KORLAS' ? korlasClassName : 'Semua Kelas'})</h3>
                <button onClick={() => triggerPdfEmailDialog('LEDGER')} className="bg-indigo-600 text-white px-4 py-2 rounded-2xl text-xs font-bold shadow-xs">
                  📄 Export PDF & Kirim Email Laporan
                </button>
              </div>
              <div className="overflow-x-auto">
                <table className="w-full text-left text-xs text-slate-700">
                  <thead className="bg-slate-100 uppercase text-[10px] text-slate-500">
                    <tr>
                      <th className="p-3">Tipe</th>
                      <th className="p-3">Kategori / Sumber Asal</th>
                      <th className="p-3">Keterangan</th>
                      <th className="p-3">Kelas</th>
                      <th className="p-3">Metode</th>
                      <th className="p-3">Waktu</th>
                      <th className="p-3 text-right">Jumlah</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-slate-100">
                    {scopedTransactions.map((tx) => (
                      <tr key={tx.id}>
                        <td className="p-3 font-bold">{tx.type}</td>
                        <td className="p-3 font-bold text-indigo-700">{tx.category}</td>
                        <td className="p-3">{tx.description}</td>
                        <td className="p-3 font-bold text-indigo-600">{tx.className || 'Kelas 5-A'}</td>
                        <td className="p-3">{tx.paymentMethod}</td>
                        <td className="p-3">{tx.date}</td>
                        <td className="p-3 text-right font-black">{formatIDR(tx.amount)}</td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>
          )}

          {/* TAB 3: STUDENTS DIRECTORY */}
          {activeTab === 'STUDENTS' && (
            <div className="light-card rounded-3xl p-6 space-y-4">
              <div className="flex justify-between items-center pb-3 border-b border-slate-100">
                <div>
                  <h3 className="text-lg font-bold text-slate-900">Direktori Siswa Terdaftar ({currentUser.role === 'KORLAS' ? korlasClassName : 'Semua Kelas'})</h3>
                  <p className="text-xs text-indigo-600 font-semibold">💡 Klik nama siswa untuk melihat detail informasi & history pembayaran siswa tersebut</p>
                </div>
                <button
                  onClick={() => setShowStudentModal(true)}
                  className="bg-emerald-600 text-white px-4 py-2.5 rounded-2xl text-xs font-bold shadow-md"
                >
                  + Tambah Siswa Baru
                </button>
              </div>

              <div className="overflow-x-auto">
                <table className="w-full text-left text-xs text-slate-700">
                  <thead className="bg-slate-100 uppercase text-[10px] text-slate-500">
                    <tr>
                      <th className="p-3">Nama Siswa (Klik Detail)</th>
                      <th className="p-3">Kelas</th>
                      <th className="p-3">Jenis Kelamin</th>
                      <th className="p-3">Email Login Siswa</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-slate-100">
                    {scopedStudents.map((s) => (
                      <tr
                        key={s.id}
                        onClick={() => { setSelectedStudentDetail(s); setShowStudentDetailModal(true); }}
                        className="hover:bg-indigo-50 cursor-pointer transition-colors"
                      >
                        <td className="p-3 font-bold text-indigo-900 underline">{s.name} 🔍</td>
                        <td className="p-3 font-bold text-indigo-600">{s.enrollments?.[0]?.class?.name || '-'}</td>
                        <td className="p-3">{s.gender === 'MALE' ? 'Laki-laki (L)' : 'Perempuan (P)'}</td>
                        <td className="p-3 text-slate-500 font-mono">{s.email}</td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>
          )}

          {/* TAB 4: REPORTS */}
          {activeTab === 'REPORTS' && (
            <div className="light-card rounded-3xl p-6 space-y-6">
              <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-3 border-b border-slate-100">
                <div>
                  <h3 className="text-lg font-bold text-slate-900">Laporan Kelunasan Iuran Siswa ({currentUser.role === 'KORLAS' ? korlasClassName : 'Semua Kelas'})</h3>
                  <p className="text-xs text-slate-500">Rekapitulasi tagihan per periode bulan dan history setoran siswa</p>
                </div>

                <div className="flex flex-wrap gap-2 items-center">
                  <select
                    value={reportPeriodFilter}
                    onChange={(e) => setReportPeriodFilter(e.target.value)}
                    className="bg-slate-100 border border-slate-200 rounded-2xl px-3 py-2 text-xs font-bold"
                  >
                    <option value="Agustus 2026">Periode: Agustus 2026</option>
                    <option value="September 2026">Periode: September 2026</option>
                    <option value="ALL">Semua Periode Bulan</option>
                  </select>

                  <div className="flex bg-slate-100 p-1 rounded-2xl text-xs font-bold">
                    <button onClick={() => setReportStatusFilter('ALL')} className={`px-3 py-1.5 rounded-xl ${reportStatusFilter === 'ALL' ? 'bg-white font-black' : ''}`}>Semua</button>
                    <button onClick={() => setReportStatusFilter('PAID')} className={`px-3 py-1.5 rounded-xl ${reportStatusFilter === 'PAID' ? 'bg-emerald-600 text-white font-black' : ''}`}>Lunas</button>
                    <button onClick={() => setReportStatusFilter('PENDING')} className={`px-3 py-1.5 rounded-xl ${reportStatusFilter === 'PENDING' ? 'bg-amber-600 text-white font-black' : ''}`}>Belum Lunas</button>
                  </div>

                  <button onClick={() => triggerPdfEmailDialog('REPORTS')} className="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-2xl text-xs font-bold shadow-md">
                    📄 Export PDF & Kirim Email
                  </button>
                </div>
              </div>

              <div className="overflow-x-auto">
                <table className="w-full text-left text-xs text-slate-700">
                  <thead className="bg-slate-100 uppercase text-[10px] text-slate-500">
                    <tr>
                      <th className="p-3">Nama Siswa</th>
                      <th className="p-3">Kelas</th>
                      <th className="p-3">Periode</th>
                      <th className="p-3 text-right">Tagihan</th>
                      <th className="p-3 text-center">Status</th>
                      <th className="p-3">Waktu Bayar</th>
                      <th className="p-3 text-center">Aksi</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-slate-100">
                    {filteredBillings.map((b) => (
                      <tr key={b.id}>
                        <td className="p-3 font-bold text-slate-900">{b.studentName}</td>
                        <td className="p-3 font-bold text-indigo-600">{b.className || 'Kelas 5-A'}</td>
                        <td className="p-3 font-semibold text-slate-700">{b.period}</td>
                        <td className="p-3 text-right font-black">{formatIDR(b.amountDue)}</td>
                        <td className="p-3 text-center">
                          <span className={`px-2.5 py-0.5 rounded-full text-[10px] font-bold ${b.status === 'PAID' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'}`}>
                            {b.status === 'PAID' ? 'LUNAS' : 'BELUM LUNAS'}
                          </span>
                        </td>
                        <td className="p-3 text-slate-500">{b.paidAt || 'Belum ada setoran'}</td>
                        <td className="p-3 text-center">
                          {b.status === 'PENDING' && (
                            <button onClick={() => { setSelectedBilling(b); setPayAmountPaid(b.amountDue.toString()); setPayExcessAction('Infaq'); setShowPayModal(true); }} className="bg-emerald-600 text-white px-3 py-1.5 rounded-xl text-[11px] font-bold shadow-xs">
                              Catat Pelunasan
                            </button>
                          )}
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>
          )}

          {/* TAB 5: ACCOUNT MANAGEMENT */}
          {activeTab === 'USERS' && (currentUser.role === 'ADMIN' || currentUser.role === 'SUPER_ADMIN') && (
            <div className="light-card rounded-3xl p-6 space-y-6">
              <div className="flex justify-between items-center pb-3 border-b border-slate-100">
                <div>
                  <h3 className="text-lg font-bold text-slate-900">Account Management</h3>
                  <p className="text-xs text-slate-500">Pendaftaran akun Admin, Bendahara, Korlas, dan Orang Tua Siswa</p>
                </div>
                <button onClick={() => { setEditingUserId(null); setNewUserName(''); setNewUserEmail(''); setNewUserRole(currentUser?.role === 'SUPER_ADMIN' ? 'ADMIN' : 'KORLAS'); setShowUserModal(true); }} className="bg-indigo-600 text-white px-4 py-2.5 rounded-2xl text-xs font-bold shadow-md">
                  + Tambah Account (Admin / Bendahara / Korlas / Parent)
                </button>
              </div>
              <div className="overflow-x-auto">
                <table className="w-full text-left text-xs text-slate-700">
                  <thead className="bg-slate-100 uppercase text-[10px] text-slate-500">
                    <tr>
                      <th className="p-3">Nama</th>
                      <th className="p-3">Email</th>
                      <th className="p-3">Role</th>
                      <th className="p-3">Cakupan Kelas / Anak</th>
                      <th className="p-3 text-center">Aksi</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-slate-100">
                    {userAccounts.filter(u => u.schoolId === activeSchoolId || (currentUser?.role === 'SUPER_ADMIN' && u.role === 'SUPER_ADMIN')).map((u) => (
                      <tr key={u.id}>
                        <td className="p-3 font-bold">{u.name}</td>
                        <td className="p-3">{u.email}</td>
                        <td className="p-3"><span className="font-bold text-indigo-700">{formatRole(u.role)}</span></td>
                        <td className="p-3 font-semibold text-slate-800">
                          {u.role === 'KORLAS' ? `📌 Korlas: ${classes.find((c: any) => c.id === u.managedClass)?.name || u.managedClass || 'Kelas 5-A'}` : u.studentName ? `👨‍👦 Anak: ${u.studentName}` : '-'}
                        </td>
                        <td className="p-3 text-center">
                          <button onClick={() => handleEditUserClick(u)} className="text-indigo-600 hover:text-indigo-800 mr-3 text-[11px] font-bold">Edit</button>
                          <button onClick={() => handleDeleteUserClick(u.id)} className="text-rose-600 hover:text-rose-800 text-[11px] font-bold">Hapus</button>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>
          )}

          {activeTab === 'CLASSES' && (currentUser.role === 'ADMIN' || currentUser.role === 'SUPER_ADMIN') && (
            <div className="space-y-6">
              {!activeSchoolId && currentUser.role === 'SUPER_ADMIN' ? (
                <div className="bg-white rounded-3xl p-12 text-center border border-slate-100 shadow-sm flex flex-col items-center justify-center">
                  <div className="h-16 w-16 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center text-3xl mb-4">
                    🏢
                  </div>
                  <h3 className="text-xl font-black text-slate-800 mb-2">Pilih Sekolah Terlebih Dahulu</h3>
                  <p className="text-slate-500 max-w-md mx-auto">
                    Sebagai Super Admin, Anda harus memilih salah satu sekolah pada dropdown di bagian atas layar untuk mengelola data kelas dan tahun ajaran spesifik pada sekolah tersebut.
                  </p>
                </div>
              ) : (
                <>
                  <div className="flex flex-col md:flex-row justify-between items-start md:items-center bg-white p-6 rounded-3xl shadow-sm border border-slate-100 gap-4">
                    <div>
                      <h3 className="text-lg font-bold text-slate-900">Data Kelas & Tahun Ajaran</h3>
                      <p className="text-xs text-slate-500">Kelola master data kelas dan tahun ajaran aktif</p>
                    </div>
                    <div className="flex gap-3">
                  <button onClick={() => { 
                    if (!activeSchoolId) { showToast('❌ Silakan pilih sekolah aktif terlebih dahulu!'); return; }
                    setEditingAcademicYearId(null); setNewAcademicYearName(''); setShowAcademicYearModal(true); 
                  }} className="bg-emerald-600 text-white px-4 py-2.5 rounded-2xl text-xs font-bold shadow-md hover:bg-emerald-700 transition-colors">
                    + Tambah Tahun Ajaran
                  </button>
                  <button onClick={() => { 
                    if (!activeSchoolId) { showToast('❌ Silakan pilih sekolah aktif terlebih dahulu!'); return; }
                    setEditingClassId(null); setNewClassName(''); setNewClassAcademicYearId(''); setShowClassModal(true); 
                  }} className="bg-indigo-600 text-white px-4 py-2.5 rounded-2xl text-xs font-bold shadow-md hover:bg-indigo-700 transition-colors">
                    + Tambah Kelas
                  </button>
                </div>
              </div>

              {/* Data Table for Academic Years & Classes */}
              <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div className="lg:col-span-1 bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                  <div className="p-4 border-b border-slate-100 bg-slate-50">
                    <h4 className="font-bold text-slate-800 text-sm">Tahun Ajaran</h4>
                  </div>
                  <div className="p-4 space-y-3 max-h-[500px] overflow-y-auto">
                    {academicYears.map((ay: any) => (
                      <div key={ay.id} className={`p-4 rounded-2xl border ${ay.isCurrent ? 'bg-indigo-50 border-indigo-200' : 'bg-white border-slate-200'}`}>
                        <div className="flex justify-between items-center mb-2">
                          <span className="font-bold text-slate-800 text-sm">{ay.year}</span>
                          {ay.isCurrent && <span className="bg-indigo-600 text-white text-[10px] px-2 py-1 rounded-full font-bold">Aktif</span>}
                        </div>
                        <div className="flex justify-end gap-2 mt-2">
                          <button onClick={() => handleEditAcademicYearClick(ay)} className="text-indigo-600 hover:text-indigo-800 text-[10px] font-bold px-2 py-1 bg-white border border-indigo-100 rounded-lg hover:bg-indigo-50 transition-colors">Edit</button>
                          <button onClick={() => handleDeleteAcademicYear(ay.id)} className="text-rose-600 hover:text-rose-800 text-[10px] font-bold px-2 py-1 bg-white border border-rose-100 rounded-lg hover:bg-rose-50 transition-colors">Hapus</button>
                        </div>
                      </div>
                    ))}
                    {academicYears.length === 0 && <p className="text-xs text-slate-400 text-center py-4">Belum ada data tahun ajaran.</p>}
                  </div>
                </div>

                <div className="lg:col-span-2 bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                  <div className="p-4 border-b border-slate-100 bg-slate-50">
                    <h4 className="font-bold text-slate-800 text-sm">Daftar Kelas</h4>
                  </div>
                  <div className="overflow-x-auto">
                    <table className="w-full text-left text-sm text-slate-600">
                      <thead className="bg-slate-50 text-xs uppercase text-slate-500 font-bold border-b border-slate-100">
                        <tr>
                          <th className="p-4">Nama Kelas</th>
                          <th className="p-4">Tahun Ajaran</th>
                          <th className="p-4">Total Siswa</th>
                          <th className="p-4 text-center">Aksi</th>
                        </tr>
                      </thead>
                      <tbody className="divide-y divide-slate-100">
                        {classes.map((cls: any) => (
                          <tr key={cls.id} className="hover:bg-slate-50/50 transition-colors">
                            <td className="p-4 font-bold text-slate-900">{cls.name}</td>
                            <td className="p-4 font-medium text-slate-700">{cls.academicYear?.year || '-'}</td>
                            <td className="p-4 text-slate-500">
                              <span className="bg-slate-100 px-3 py-1 rounded-xl text-xs font-bold text-slate-600">{cls.enrollments?.length || 0} Siswa</span>
                            </td>
                            <td className="p-4 text-center">
                              <button onClick={() => handleEditClassClick(cls)} className="text-indigo-600 hover:text-indigo-800 mr-3 text-[11px] font-bold bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-xl transition-colors">Edit</button>
                              <button onClick={() => handleDeleteClass(cls.id)} className="text-rose-600 hover:text-rose-800 text-[11px] font-bold bg-rose-50 hover:bg-rose-100 px-3 py-1.5 rounded-xl transition-colors">Hapus</button>
                            </td>
                          </tr>
                        ))}
                        {classes.length === 0 && (
                          <tr><td colSpan={4} className="p-6 text-center text-slate-400 text-xs">Belum ada data kelas.</td></tr>
                        )}
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
              </>
              )}
            </div>
          )}
        </main>
      </div>

      {/* Compact Slim Application Footer with Social Links */}
      <footer className="mt-6 py-3 border-t border-slate-200 text-center text-[11px] text-slate-500 bg-white shadow-xs">
        <div className="w-full px-6 flex flex-col sm:flex-row items-center justify-between gap-2">
          <p className="font-semibold text-slate-700">
            🏫 {activeSchool?.name || 'Sekolah'} • Sistem Informasi Keuangan Kas Sekolah
          </p>

          {/* Social Media Contact Links */}
          <div className="flex flex-wrap items-center justify-center gap-4 font-medium text-slate-600">
            <a href="https://facebook.com" target="_blank" rel="noreferrer" className="hover:text-indigo-600 transition-colors flex items-center gap-1">
              📘 Facebook
            </a>
            <a href="https://twitter.com" target="_blank" rel="noreferrer" className="hover:text-sky-500 transition-colors flex items-center gap-1">
              🐦 Twitter/X
            </a>
            <a href="https://instagram.com" target="_blank" rel="noreferrer" className="hover:text-pink-600 transition-colors flex items-center gap-1">
              📸 Instagram
            </a>
            <a href="https://wa.me/628159360460" target="_blank" rel="noreferrer" className="hover:text-emerald-600 font-bold transition-colors flex items-center gap-1">
              💬 WhatsApp (Hubungi Kami)
            </a>
          </div>

          <p className="text-[10px] text-slate-400">
            Developed by Uwais Syahdan Riano • v1.2.0
          </p>
        </div>
      </footer>

      {/* Modal 1: Admin Hero Media Customizer Modal */}
      {showMediaModal && (currentUser.role === 'ADMIN' || currentUser.role === 'SUPER_ADMIN') && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
          <div className="bg-white w-full max-w-lg p-6 rounded-3xl border border-slate-200 shadow-2xl space-y-4">
            <div className="flex justify-between items-center pb-3 border-b border-slate-100">
              <h3 className="text-base font-bold text-slate-900">🖼️ Ganti Foto / Video Header Login Sekolah</h3>
              <button onClick={() => setShowMediaModal(false)} className="text-slate-400 text-lg">✕</button>
            </div>

            <form onSubmit={handleSaveHeroMedia} className="space-y-4 text-xs">
              <div className="p-4 bg-purple-50 border border-purple-200 rounded-2xl space-y-2">
                <label className="block text-purple-900 font-bold mb-1">
                  📁 Upload File Foto / Video dari Komputer Local
                </label>
                <input
                  type="file"
                  accept="image/*,video/*"
                  onChange={handleFileUpload}
                  className="w-full bg-white border border-purple-300 rounded-xl p-2.5 text-xs text-slate-800 font-medium file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-purple-600 file:text-white hover:file:bg-purple-700 cursor-pointer"
                />
                <p className="text-[10px] text-purple-700">
                  Format didukung: JPG, PNG, WEBP, MP4, WEBM (Otomatis mendeteksi foto atau video).
                </p>
              </div>

              <div className="relative text-center my-2">
                <div className="absolute inset-0 flex items-center"><div className="w-full border-t border-slate-200" /></div>
                <span className="relative bg-white px-3 text-[10px] uppercase font-bold text-slate-400">Atau Masukkan Link URL</span>
              </div>

              <div>
                <label className="block text-slate-700 font-bold mb-1">Tipe Media Banner</label>
                <div className="grid grid-cols-2 gap-2">
                  <button
                    type="button"
                    onClick={() => setInputMediaType('IMAGE')}
                    className={`py-2.5 rounded-2xl font-bold border ${inputMediaType === 'IMAGE' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-slate-50 text-slate-600'}`}
                  >
                    📷 Foto / Gambar (JPG/PNG)
                  </button>
                  <button
                    type="button"
                    onClick={() => setInputMediaType('VIDEO')}
                    className={`py-2.5 rounded-2xl font-bold border ${inputMediaType === 'VIDEO' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-slate-50 text-slate-600'}`}
                  >
                    🎥 Video MP4 Sekolah
                  </button>
                </div>
              </div>

              <div>
                <label className="block text-slate-700 font-bold mb-1">URL / Data Media Banner Sekolah</label>
                <input
                  type="text"
                  required
                  placeholder="https://domain-sekolah.sch.id/banner.jpg"
                  value={inputMediaUrl}
                  onChange={(e) => setInputMediaUrl(e.target.value)}
                  className="w-full bg-slate-50 border border-slate-300 rounded-2xl p-3.5 text-slate-900 text-sm font-mono"
                />
              </div>

              {/* Preview */}
              <div className="p-3 bg-slate-100 rounded-2xl border border-slate-200 space-y-1">
                <p className="font-bold text-slate-700 text-[11px]">Preview Media Login:</p>
                <div className="h-32 rounded-xl overflow-hidden bg-slate-900 flex items-center justify-center">
                  {inputMediaType === 'VIDEO' ? (
                    <video src={inputMediaUrl} autoPlay loop muted className="w-full h-full object-cover" />
                  ) : (
                    <img src={inputMediaUrl} alt="Preview" className="w-full h-full object-cover" />
                  )}
                </div>
              </div>

              <div className="flex space-x-3 pt-2">
                <button type="button" onClick={() => setShowMediaModal(false)} className="flex-1 py-3 rounded-2xl bg-slate-100 text-slate-700 font-bold">Batal</button>
                <button type="submit" className="flex-1 py-3 rounded-2xl gradient-button-school text-white font-bold shadow-md">Simpan & Terapkan Banner</button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Modal 2: Email PDF Dialog Popup */}
      {showPdfEmailModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
          <div className="bg-white w-full max-w-md p-6 rounded-3xl border border-slate-200 shadow-2xl space-y-4">
            <div className="flex justify-between items-center pb-3 border-b border-slate-100">
              <h3 className="text-base font-bold text-slate-900">📄 Export PDF & Kirim Laporan ke Email</h3>
              <button onClick={() => setShowPdfEmailModal(false)} className="text-slate-400 text-lg">✕</button>
            </div>

            <form onSubmit={handleExecutePdfAndSendEmail} className="space-y-4 text-xs">
              <div>
                <label className="block text-slate-700 font-bold mb-1">Email Tujuan Penerima Laporan PDF</label>
                <input
                  type="email"
                  required
                  placeholder="admin@sdn08pagi.sch.id"
                  value={targetEmail}
                  onChange={(e) => setTargetEmail(e.target.value)}
                  className="w-full bg-slate-50 border border-slate-300 rounded-2xl p-3.5 text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                />
              </div>

              <div className="p-3 bg-indigo-50 border border-indigo-200 rounded-2xl text-[11px] text-indigo-900">
                📧 <strong>Format Email Rapi:</strong> Sistem akan mengunduh dokumen PDF secara lokal dan sekaligus mendistribusikan email laporan berformat HTML lengkap beserta lampiran PDF ke email yang dimasukkan.
              </div>

              <div className="flex space-x-3 pt-2">
                <button type="button" onClick={() => setShowPdfEmailModal(false)} className="flex-1 py-3 rounded-2xl bg-slate-100 text-slate-700 font-bold">Batal</button>
                <button type="submit" className="flex-1 py-3 rounded-2xl gradient-button-school text-white font-bold shadow-md">Kirim Email & Download PDF</button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Modal 3: Student Detail & Payment History Modal */}
      {showStudentDetailModal && selectedStudentDetail && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
          <div className="bg-white w-full max-w-2xl p-6 rounded-3xl border border-slate-200 shadow-2xl space-y-6 max-h-[90vh] overflow-y-auto">
            <div className="flex justify-between items-center pb-3 border-b border-slate-100">
              <div>
                <h3 className="text-lg font-bold text-slate-900">Detail Informasi & Rekapitulasi Siswa 🎓</h3>
                <p className="text-xs text-slate-500">{selectedStudentDetail.name} • {selectedStudentDetail.className || 'Kelas 5-A'}</p>
              </div>
              <button onClick={() => setShowStudentDetailModal(false)} className="text-slate-400 text-lg">✕</button>
            </div>

            <div className="grid grid-cols-2 gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-200 text-xs">
              <div><p className="text-slate-500">Kelas:</p><p className="font-bold text-indigo-600">{selectedStudentDetail.className || 'Kelas 5-A'}</p></div>
              <div><p className="text-slate-500">Jenis Kelamin:</p><p className="font-semibold">{selectedStudentDetail.gender === 'MALE' ? 'Laki-laki (L)' : 'Perempuan (P)'}</p></div>
              <div><p className="text-slate-500">Email Login:</p><p className="font-mono text-slate-800 text-[11px]">{selectedStudentDetail.email}</p></div>
            </div>

            {/* History Payments for this student */}
            <div className="space-y-3">
              <h4 className="text-sm font-bold text-slate-900">Riwayat Pembayaran & Dues Siswa Ini</h4>
              <div className="overflow-x-auto">
                <table className="w-full text-left text-xs text-slate-700">
                  <thead className="bg-slate-100 uppercase text-[10px] text-slate-500">
                    <tr>
                      <th className="p-2.5">Program Iuran</th>
                      <th className="p-2.5">Periode</th>
                      <th className="p-2.5 text-right">Tagihan</th>
                      <th className="p-2.5 text-center">Status</th>
                      <th className="p-2.5">Waktu Setor</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-slate-100">
                    {billings.filter(b => b.studentId === selectedStudentDetail.id).map((b) => (
                      <tr key={b.id}>
                        <td className="p-2.5 font-semibold text-slate-900">{b.schemeTitle}</td>
                        <td className="p-2.5 text-slate-600">{b.period}</td>
                        <td className="p-2.5 text-right font-bold">{formatIDR(b.amountDue)}</td>
                        <td className="p-2.5 text-center">
                          <span className={`px-2 py-0.5 rounded text-[10px] font-bold ${b.status === 'PAID' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'}`}>
                            {b.status === 'PAID' ? 'LUNAS' : 'PENDING'}
                          </span>
                        </td>
                        <td className="p-2.5 text-slate-500">{b.paidAt || '-'}</td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>

            <div className="text-right">
              <button onClick={() => setShowStudentDetailModal(false)} className="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold">Tutup</button>
            </div>
          </div>
        </div>
      )}

      {/* Modal 4: Profile Change Password */}
      {showProfileModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
          <div className="bg-white w-full max-w-md p-6 rounded-3xl border border-slate-200 shadow-2xl space-y-4">
            <div className="flex justify-between items-center pb-3 border-b border-slate-100">
              <h3 className="text-base font-bold text-slate-900">Profil Saya & Ubah Password ⚙️</h3>
              <button onClick={() => setShowProfileModal(false)} className="text-slate-400 text-lg">✕</button>
            </div>

            <form onSubmit={handleUpdateProfile} className="space-y-4 text-xs">
              <div>
                <label className="block text-slate-700 font-bold mb-1">Nama Pengguna</label>
                <input type="text" required value={profileName} onChange={(e) => setProfileName(e.target.value)} className="w-full bg-slate-50 border border-slate-300 rounded-2xl p-3.5 text-slate-900 text-sm" />
              </div>
              <div>
                <label className="block text-slate-700 font-bold mb-1">Email</label>
                <input type="email" disabled value={currentUser.email} className="w-full bg-slate-100 border border-slate-200 rounded-2xl p-3.5 text-slate-500 text-sm" />
              </div>
              <div>
                <label className="block text-slate-700 font-bold mb-1">Password Saat Ini</label>
                <input type="password" placeholder="Password Saat Ini" value={oldPassword} onChange={(e) => setOldPassword(e.target.value)} className="w-full bg-slate-50 border border-slate-300 rounded-2xl p-3.5 text-slate-900 text-sm" />
              </div>
              <div>
                <label className="block text-slate-700 font-bold mb-1">Password Baru</label>
                <input type="password" placeholder="Password Baru" value={newPassword} onChange={(e) => setNewPassword(e.target.value)} className="w-full bg-slate-50 border border-slate-300 rounded-2xl p-3.5 text-slate-900 text-sm" />
              </div>

              <div className="flex space-x-3 pt-2">
                <button type="button" onClick={() => setShowProfileModal(false)} className="flex-1 py-3 rounded-2xl bg-slate-100 text-slate-700 font-bold">Batal</button>
                <button type="submit" className="flex-1 py-3 rounded-2xl gradient-button-school text-white font-bold shadow-md">Simpan Perubahan</button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Modal 5: Student Direct Registration Modal */}
      {showStudentModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
          <div className="bg-white w-full max-w-lg p-6 rounded-3xl border border-slate-200 shadow-2xl space-y-4">
            <div className="flex justify-between items-center pb-3 border-b border-slate-100">
              <h3 className="text-base font-bold text-slate-900">
                Tambah Siswa Baru
              </h3>
              <button onClick={() => { setShowStudentModal(false); setStudentValidationError(''); }} className="text-slate-400 text-lg">✕</button>
            </div>

            {studentValidationError && (
              <div className="p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-600 text-xs font-bold">
                {studentValidationError}
              </div>
            )}

            <form onSubmit={handleAddStudentDirect} className="space-y-4 text-xs">
              <div>
                <label className="block text-slate-700 font-bold mb-1">Nama Lengkap Siswa</label>
                <input type="text" required placeholder="Nama Lengkap" value={studentName} onChange={(e) => setStudentName(e.target.value)} className="w-full bg-slate-50 border border-slate-300 rounded-2xl p-3.5 text-slate-900 text-sm" />
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-slate-700 font-bold mb-1">Kelas</label>
                  <select
                    required
                    disabled={currentUser.role === 'KORLAS'}
                    value={currentUser.role === 'KORLAS' && currentUser.managedClass ? currentUser.managedClass : studentClass}
                    onChange={(e) => setStudentClass(e.target.value)}
                    className={`w-full border rounded-2xl p-3.5 text-sm font-bold ${currentUser.role === 'KORLAS' ? 'bg-slate-100 border-slate-200 text-slate-500' : 'bg-slate-50 border-slate-300 text-slate-900'}`}
                  >
                    <option value="">-- Pilih Kelas --</option>
                    {classes.map((cls: any) => (
                      <option key={cls.id} value={cls.id}>{cls.name} ({cls.academicYear?.year})</option>
                    ))}
                  </select>
                </div>
                <div>
                  <label className="block text-slate-700 font-bold mb-1">Jenis Kelamin</label>
                  <select value={studentGender} onChange={(e) => setStudentGender(e.target.value as any)} className="w-full bg-slate-50 border border-slate-300 rounded-2xl p-3.5 text-slate-900 text-sm font-semibold">
                    <option value="MALE">Laki-laki</option>
                    <option value="FEMALE">Perempuan</option>
                  </select>
                </div>
              </div>

              <div>
                <label className="block text-slate-700 font-bold mb-1">Email Login Siswa</label>
                <input type="email" placeholder="siswa@sdn08pagi.sch.id" value={studentEmail} onChange={(e) => setStudentEmail(e.target.value)} className="w-full bg-slate-50 border border-slate-300 rounded-2xl p-3.5 text-slate-900 text-sm" />
              </div>

              <div className="flex space-x-3 pt-2">
                <button type="button" onClick={() => { setShowStudentModal(false); setStudentValidationError(''); }} className="flex-1 py-3 rounded-2xl bg-slate-100 text-slate-700 font-bold">Batal</button>
                <button type="submit" className="flex-1 py-3 rounded-2xl bg-emerald-600 text-white font-bold shadow-md">Simpan & Buat Akun</button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Modal 6: Categorized Cash Income Mutation */}
      {showTransactionModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
          <div className="bg-white w-full max-w-md p-6 rounded-3xl border border-slate-200 shadow-2xl space-y-4">
            <div className="flex justify-between items-center pb-3 border-b border-slate-100">
              <h3 className="text-base font-bold text-slate-900">Catat Mutasi Kas ({currentUser.role === 'KORLAS' ? korlasClassName : 'Kelas'})</h3>
              <button onClick={() => setShowTransactionModal(false)} className="text-slate-400 text-lg">✕</button>
            </div>

            <form onSubmit={handleRecordTransaction} className="space-y-4 text-xs">
              <div>
                <label className="block text-slate-700 font-bold mb-1">Tipe Mutasi</label>
                <div className="grid grid-cols-2 gap-2">
                  <button type="button" onClick={() => setTxnType('INCOME')} className={`py-2.5 rounded-2xl font-bold border ${txnType === 'INCOME' ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-slate-50 text-slate-600'}`}>+ Pemasukan</button>
                  <button type="button" onClick={() => setTxnType('EXPENSE')} className={`py-2.5 rounded-2xl font-bold border ${txnType === 'EXPENSE' ? 'bg-rose-600 text-white border-rose-600' : 'bg-slate-50 text-slate-600'}`}>- Pengeluaran</button>
                </div>
              </div>

              {txnType === 'INCOME' && (
                <div>
                  <label className="block text-slate-700 font-bold mb-1">Sumber Asal Pemasukan</label>
                  <select value={incomeSource} onChange={(e) => setIncomeSource(e.target.value)} className="w-full bg-slate-50 border border-slate-300 rounded-2xl p-3.5 text-slate-900 text-sm font-bold">
                    <option value="Iuran Kas Siswa">1. Iuran Kas Siswa</option>
                    <option value="Sumbangan / Donasi Orang Tua">2. Sumbangan / Donasi Orang Tua</option>
                    <option value="Dana Bantuan / Sponsor">3. Dana Bantuan Sekolah / Sponsor</option>
                    <option value="Hasil Bazzar / Event Kelas">4. Hasil Bazzar / Event Kelas</option>
                    <option value="Lain-lain">5. Lain-lain</option>
                  </select>
                </div>
              )}

              {currentUser?.role !== 'KORLAS' && (
                <div>
                  <label className="block text-slate-700 font-bold mb-1">Pilih Kelas</label>
                  <select
                    required
                    value={txnClass}
                    onChange={(e) => setTxnClass(e.target.value)}
                    className="w-full bg-slate-50 border border-slate-300 rounded-2xl p-3.5 text-slate-900 text-sm font-bold"
                  >
                    <option value="" disabled>-- Pilih Kelas --</option>
                    {classes.map((cls) => (
                      <option key={cls.id} value={cls.id}>
                        {cls.name}
                      </option>
                    ))}
                  </select>
                </div>
              )}

              <div>
                <label className="block text-slate-700 font-bold mb-1">Jumlah (IDR)</label>
                <input type="number" required placeholder="25000" value={txnAmount} onChange={(e) => setTxnAmount(e.target.value)} className="w-full bg-slate-50 border border-slate-300 rounded-2xl p-3.5 text-slate-900 text-sm font-medium" />
              </div>

              <div>
                <label className="block text-slate-700 font-bold mb-1">Keterangan Detail</label>
                <textarea rows={3} placeholder="Detail mutasi kas..." value={txnDescription} onChange={(e) => setTxnDescription(e.target.value)} className="w-full bg-slate-50 border border-slate-300 rounded-2xl p-3.5 text-slate-900 text-sm font-medium"></textarea>
              </div>

              <div className="flex space-x-3 pt-2">
                <button type="button" onClick={() => setShowTransactionModal(false)} className="flex-1 py-3 rounded-2xl bg-slate-100 text-slate-700 font-bold">Batal</button>
                <button type="submit" className="flex-1 py-3 rounded-2xl gradient-button-school text-white font-bold shadow-md">Simpan Mutasi Kas</button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Modal 7: Automated Cron Dues Scheduler */}
      {showScheduleModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
          <div className="bg-white w-full max-w-md p-6 rounded-3xl border border-slate-200 shadow-2xl space-y-4">
            <div className="flex justify-between items-center pb-3 border-b border-slate-100">
              <h3 className="text-base font-bold text-slate-900">📅 Penjadwalan Iuran Otomatis ({currentUser.role === 'KORLAS' ? korlasClassName : 'Multi-Kelas'})</h3>
              <button onClick={() => setShowScheduleModal(false)} className="text-slate-400 text-lg">✕</button>
            </div>

            <form onSubmit={handleSaveCronSchedule} className="space-y-4 text-xs">
              <div>
                <label className="block text-slate-700 font-bold mb-1">Program Iuran Wajib</label>
                <input type="text" required value={cronTitle} onChange={(e) => setCronTitle(e.target.value)} className="w-full bg-slate-50 border border-slate-300 rounded-2xl p-3.5 text-slate-900 text-sm font-medium" />
              </div>

              {currentUser?.role !== 'KORLAS' && (
                <div>
                  <label className="block text-slate-700 font-bold mb-1">Pilih Kelas</label>
                  <select
                    required
                    value={cronClass}
                    onChange={(e) => setCronClass(e.target.value)}
                    className="w-full bg-slate-50 border border-slate-300 rounded-2xl p-3.5 text-slate-900 text-sm font-bold"
                  >
                    <option value="" disabled>-- Pilih Kelas --</option>
                    {classes.map((cls) => (
                      <option key={cls.id} value={cls.id}>{cls.name}</option>
                    ))}
                  </select>
                </div>
              )}

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-slate-700 font-bold mb-1">Tanggal Eksekusi Rutin</label>
                  <select value={cronDay} onChange={(e) => setCronDay(parseInt(e.target.value))} className="w-full bg-slate-50 border border-slate-300 rounded-2xl p-3.5 text-slate-900 text-sm font-bold">
                    <option value={1}>Setiap Tanggal 1</option>
                    <option value={5}>Setiap Tanggal 5</option>
                    <option value={10}>Setiap Tanggal 10</option>
                  </select>
                </div>
                <div>
                  <label className="block text-slate-700 font-bold mb-1">Nominal (IDR)</label>
                  <input type="number" required value={cronAmount} onChange={(e) => setCronAmount(parseInt(e.target.value))} className="w-full bg-slate-50 border border-slate-300 rounded-2xl p-3.5 text-slate-900 text-sm font-medium" />
                </div>
              </div>

              <div className="p-3.5 bg-purple-50 border border-purple-200 rounded-2xl text-[11px] text-purple-900">
                🔒 <strong>Isolasi Korlas:</strong> Tagihan otomatis ini hanya akan dihasilkan secara terbatas untuk siswa di <strong>{currentUser.role === 'KORLAS' ? korlasClassName : 'Kelas Anda'}</strong> tanpa mempengaruhi jadwal kelas lain.
              </div>

              <div className="flex space-x-3 pt-2">
                <button type="button" onClick={() => setShowScheduleModal(false)} className="flex-1 py-3 rounded-2xl bg-slate-100 text-slate-700 font-bold">Batal</button>
                <button type="submit" className="flex-1 py-3 rounded-2xl bg-amber-600 hover:bg-amber-700 text-white font-bold shadow-md">Simpan Jadwal</button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Modal 8: Admin School List Modal */}
      {showSchoolListModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
          <div className="bg-white rounded-3xl w-full max-w-2xl overflow-hidden shadow-2xl flex flex-col max-h-[80vh]">
            <div className="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50">
              <div>
                <h3 className="text-xl font-black text-slate-900">Manajemen Sekolah</h3>
                <p className="text-xs text-slate-500 mt-1">Daftar sekolah yang terdaftar di dalam sistem</p>
              </div>
              <div className="flex items-center space-x-4">
                <button onClick={() => { setEditingSchoolId(null); setNewSchoolName(''); setNewSchoolCode(''); setShowSchoolModal(true); }} className="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-xl text-xs font-bold shadow-sm transition-colors">
                  + Tambah Sekolah
                </button>
                <button onClick={() => setShowSchoolListModal(false)} className="text-slate-400 text-lg">✕</button>
              </div>
            </div>
            <div className="p-0 overflow-y-auto">
              <table className="w-full text-left text-sm">
                <thead className="bg-slate-50 text-slate-500 border-b border-slate-200">
                  <tr>
                    <th className="p-4 font-bold">Nama Sekolah</th>
                    <th className="p-4 font-bold">Kode</th>
                    <th className="p-4 font-bold text-center">Aksi</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-100">
                  {schools.map(s => (
                    <tr key={s.id} className="hover:bg-slate-50/50">
                      <td className="p-4 font-bold text-slate-900">{s.name}</td>
                      <td className="p-4 text-slate-600">{s.code}</td>
                      <td className="p-4 text-center">
                        <button onClick={() => handleEditSchoolClick(s)} className="text-indigo-600 hover:text-indigo-800 mr-3 text-xs font-bold">Edit</button>
                        <button onClick={() => handleDeleteSchool(s.id)} className="text-rose-600 hover:text-rose-800 text-xs font-bold">Hapus</button>
                      </td>
                    </tr>
                  ))}
                  {schools.length === 0 && (
                    <tr>
                      <td colSpan={3} className="p-8 text-center text-slate-400 italic">Belum ada data sekolah.</td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      )}

      {/* Modal 8.1: Admin Add/Edit School Modal */}
      {showSchoolModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
          <div className="bg-white rounded-3xl w-full max-w-md overflow-hidden shadow-2xl">
            <div className="p-6 border-b border-slate-100 flex justify-between items-center">
              <div>
                <h3 className="text-xl font-black text-slate-900">{editingSchoolId ? 'Edit Sekolah' : 'Tambah Sekolah Baru'}</h3>
                <p className="text-xs text-slate-500 mt-1">{editingSchoolId ? 'Ubah informasi sekolah' : 'Daftarkan sekolah baru ke dalam sistem'}</p>
              </div>
              <button onClick={() => setShowSchoolModal(false)} className="text-slate-400 text-lg">✕</button>
            </div>
            <form onSubmit={handleSaveSchool} className="p-6 space-y-4">
              <div>
                <label className="block text-slate-700 font-bold mb-1">Nama Sekolah</label>
                <input type="text" required placeholder="SDN 08 Pagi Jakarta" value={newSchoolName} onChange={(e) => setNewSchoolName(e.target.value)} className="w-full bg-slate-50 border border-slate-300 rounded-2xl p-3.5 text-slate-900 text-sm" />
              </div>
              <div>
                <label className="block text-slate-700 font-bold mb-1">Kode Sekolah</label>
                <input type="text" required placeholder="SDN08-JKT" value={newSchoolCode} onChange={(e) => setNewSchoolCode(e.target.value)} className="w-full bg-slate-50 border border-slate-300 rounded-2xl p-3.5 text-slate-900 text-sm" />
              </div>
              <div className="pt-4 flex gap-3">
                <button type="button" onClick={() => setShowSchoolModal(false)} className="flex-1 py-3 rounded-2xl bg-slate-100 text-slate-700 font-bold">Batal</button>
                <button type="submit" className="flex-1 py-3 rounded-2xl bg-emerald-600 text-white font-bold shadow-md">{editingSchoolId ? 'Simpan Perubahan' : 'Simpan Sekolah'}</button>
              </div>
            </form>
          </div>
        </div>
      )}

      {showAcademicYearModal && (
        <div className="fixed inset-0 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4 z-50">
          <div className="bg-white rounded-3xl w-full max-w-md shadow-2xl p-6">
            <div className="flex justify-between items-center mb-6">
              <h3 className="text-xl font-bold text-slate-900">{editingAcademicYearId ? 'Edit Tahun Ajaran' : 'Tambah Tahun Ajaran'}</h3>
              <button onClick={() => setShowAcademicYearModal(false)} className="text-slate-400 text-lg">✕</button>
            </div>
            <form onSubmit={handleCreateAcademicYear} className="space-y-4">
              <div>
                <label className="block text-slate-700 font-bold mb-1">Tahun Ajaran</label>
                <input type="text" required placeholder="Contoh: 2026/2027" value={newAcademicYearName} onChange={(e) => setNewAcademicYearName(e.target.value)} className="w-full bg-slate-50 border border-slate-300 rounded-2xl p-3.5 text-slate-900 text-sm" />
              </div>
              <div className="flex items-center gap-2">
                <input type="checkbox" id="isCurrent" checked={newAcademicYearIsCurrent} onChange={(e) => setNewAcademicYearIsCurrent(e.target.checked)} className="rounded text-emerald-600 focus:ring-emerald-500 w-4 h-4" />
                <label htmlFor="isCurrent" className="text-sm font-bold text-slate-700">Set sebagai Tahun Ajaran Aktif</label>
              </div>
              <div className="pt-4 flex gap-3">
                <button type="button" onClick={() => setShowAcademicYearModal(false)} className="flex-1 py-3 rounded-2xl bg-slate-100 text-slate-700 font-bold">Batal</button>
                <button type="submit" className="flex-1 py-3 rounded-2xl bg-emerald-600 text-white font-bold shadow-md">Simpan</button>
              </div>
            </form>
          </div>
        </div>
      )}

      {showClassModal && (
        <div className="fixed inset-0 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4 z-50">
          <div className="bg-white rounded-3xl w-full max-w-md shadow-2xl p-6">
            <div className="flex justify-between items-center mb-6">
              <h3 className="text-xl font-bold text-slate-900">{editingClassId ? 'Edit Kelas' : 'Tambah Kelas'}</h3>
              <button onClick={() => setShowClassModal(false)} className="text-slate-400 text-lg">✕</button>
            </div>
            <form onSubmit={handleCreateClass} className="space-y-4">
              <div>
                <label className="block text-slate-700 font-bold mb-1">Tahun Ajaran</label>
                <select required value={newClassAcademicYearId} onChange={(e) => setNewClassAcademicYearId(e.target.value)} className="w-full bg-slate-50 border border-slate-300 rounded-2xl p-3.5 text-slate-900 text-sm font-bold">
                  <option value="">-- Pilih Tahun Ajaran --</option>
                  {academicYears.map((ay: any) => (
                    <option key={ay.id} value={ay.id}>{ay.year}</option>
                  ))}
                </select>
              </div>
              <div>
                <label className="block text-slate-700 font-bold mb-1">Nama Kelas</label>
                <input type="text" required placeholder="Contoh: 10-IPA-1" value={newClassName} onChange={(e) => setNewClassName(e.target.value)} className="w-full bg-slate-50 border border-slate-300 rounded-2xl p-3.5 text-slate-900 text-sm" />
              </div>
              <div className="pt-4 flex gap-3">
                <button type="button" onClick={() => setShowClassModal(false)} className="flex-1 py-3 rounded-2xl bg-slate-100 text-slate-700 font-bold">Batal</button>
                <button type="submit" className="flex-1 py-3 rounded-2xl bg-indigo-600 text-white font-bold shadow-md">Simpan</button>
              </div>
            </form>
          </div>
        </div>
      )}

      {showUserModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
          <div className="bg-white w-full max-w-lg p-6 rounded-3xl border border-slate-200 shadow-2xl space-y-4">
            <div className="flex justify-between items-center pb-3 border-b border-slate-100">
              <h3 className="text-base font-bold text-slate-900">{editingUserId ? 'Edit Account Management' : 'Tambah Account Management'}</h3>
              <button onClick={() => setShowUserModal(false)} className="text-slate-400 text-lg">✕</button>
            </div>

            <form onSubmit={handleCreateUser} className="space-y-4 text-xs">
              <div>
                <label className="block text-slate-700 font-bold mb-1">Nama Lengkap</label>
                <input type="text" required placeholder="Nama User / Korlas" value={newUserName} onChange={(e) => setNewUserName(e.target.value)} className="w-full bg-slate-50 border border-slate-300 rounded-2xl p-3.5 text-slate-900 text-sm" />
              </div>

              <div>
                <label className="block text-slate-700 font-bold mb-1">Email Pengguna</label>
                <input type="email" required placeholder="user@sdn08pagi.sch.id" value={newUserEmail} onChange={(e) => setNewUserEmail(e.target.value)} className="w-full bg-slate-50 border border-slate-300 rounded-2xl p-3.5 text-slate-900 text-sm" />
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-slate-700 font-bold mb-1">Role / Hak Akses</label>
                  <select value={newUserRole} onChange={(e) => setNewUserRole(e.target.value as any)} className="w-full bg-slate-50 border border-slate-300 rounded-2xl p-3.5 text-slate-900 text-sm font-bold">
                    {currentUser?.role === 'SUPER_ADMIN' && (
                      <>
                        <option value="SUPER_ADMIN">SUPER_ADMIN (System Administrator)</option>
                        <option value="ADMIN">ADMIN (Admin Sekolah)</option>
                      </>
                    )}
                    {currentUser?.role !== 'SUPER_ADMIN' && (
                      <>
                        <option value="ADMIN">ADMIN (Admin Sekolah)</option>
                        <option value="TREASURER">TREASURER (Bendahara Sekolah)</option>
                        <option value="KORLAS">KORLAS (Koordinator Kelas)</option>
                        <option value="PARENT">PARENT (Orang Tua Murid)</option>
                      </>
                    )}
                  </select>
                </div>
                <div>
                  <label className="block text-slate-700 font-bold mb-1">Password</label>
                  <input type="password" required value={newUserPassword} onChange={(e) => setNewUserPassword(e.target.value)} className="w-full bg-slate-50 border border-slate-300 rounded-2xl p-3.5 text-slate-900 text-sm" />
                </div>
              </div>

              {newUserRole === 'KORLAS' && (
                <div className="p-4 bg-purple-50 border border-purple-200 rounded-2xl space-y-2">
                  <label className="block text-purple-900 font-bold">📌 Pilih Kelas yang Dikelola oleh Korlas Ini</label>
                  <select
                    required
                    value={selectedKorlasClass}
                    onChange={(e) => setSelectedKorlasClass(e.target.value)}
                    className="w-full bg-white border border-purple-300 rounded-xl p-3 text-slate-900 text-sm font-bold"
                  >
                    <option value="">-- Pilih Kelas --</option>
                    {classes.map((cls: any) => (
                      <option key={cls.id} value={cls.id}>{cls.name} ({cls.academicYear?.year})</option>
                    ))}
                  </select>
                </div>
              )}

              {newUserRole === 'PARENT' && (
                <div className="p-4 bg-amber-50 border border-amber-200 rounded-2xl space-y-2">
                  <label className="block text-amber-900 font-bold">👨‍👦 Pilih Nama Siswa (Anak dari Orang Tua Ini)</label>
                  <select required value={selectedChildStudentId} onChange={(e) => setSelectedChildStudentId(e.target.value)} className="w-full bg-white border border-amber-300 rounded-xl p-3 text-slate-900 text-sm font-bold">
                    <option value="">-- Pilih Siswa Terdaftar --</option>
                    {registeredStudents.map((st) => (
                      <option key={st.id} value={st.id}>{st.name} ({st.enrollments?.[0]?.class?.name || 'Belum ada kelas'})</option>
                    ))}
                  </select>
                </div>
              )}

              <div className="flex space-x-3 pt-2">
                <button type="button" onClick={() => setShowUserModal(false)} className="flex-1 py-3 rounded-2xl bg-slate-100 text-slate-700 font-bold">Batal</button>
                <button type="submit" className="flex-1 py-3 rounded-2xl gradient-button-school text-white font-bold shadow-md">{editingUserId ? 'Simpan Perubahan' : 'Simpan Account'}</button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Modal 9: Payment Settlement Modal */}
      {showPayModal && selectedBilling && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
          <div className="bg-white w-full max-w-md p-6 rounded-3xl border border-slate-200 shadow-2xl space-y-4">
            <div className="flex justify-between items-center pb-3 border-b border-slate-100">
              <h3 className="text-base font-bold text-slate-900">Catat Pelunasan Pembayaran Siswa</h3>
              <button onClick={() => setShowPayModal(false)} className="text-slate-400 text-lg">✕</button>
            </div>

            <form onSubmit={handleSettlePayment} className="space-y-4 text-xs">
              <div className="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 space-y-1">
                <p className="text-slate-500">Siswa: <span className="font-bold text-slate-900">{selectedBilling.studentName}</span> ({selectedBilling.className || 'Kelas 5-A'})</p>
                <p className="text-slate-500">Tagihan: <span className="font-bold text-indigo-600">{formatIDR(selectedBilling.amountDue)}</span></p>
              </div>

              <div>
                <label className="block text-slate-700 font-bold mb-1">Nominal Dibayarkan (Rp)</label>
                <input type="number" required value={payAmountPaid} onChange={(e) => setPayAmountPaid(e.target.value)} className="w-full bg-white border border-slate-300 rounded-2xl p-3 text-slate-900 text-sm font-bold" />
              </div>

              {parseInt(payAmountPaid || '0') > selectedBilling.amountDue && (
                <div className="p-3.5 rounded-2xl bg-amber-50 border border-amber-200 space-y-2">
                  <p className="text-amber-900 font-bold text-sm">Ada Kelebihan: {formatIDR(parseInt(payAmountPaid || '0') - selectedBilling.amountDue)}</p>
                  <div>
                    <label className="block text-amber-900 mb-1">Jadikan Sebagai Pemasukan:</label>
                    <select value={payExcessAction} onChange={(e) => setPayExcessAction(e.target.value)} className="w-full bg-white border border-amber-300 rounded-xl p-2 text-slate-900 text-sm font-bold">
                      <option value="Infaq">Infaq</option>
                      <option value="Sodaqoh">Sodaqoh</option>
                      <option value="Sumbangan">Sumbangan</option>
                      <option value="Kas Kelas">Kas Kelas</option>
                    </select>
                  </div>
                </div>
              )}

              <div>
                <label className="block text-slate-700 font-bold mb-1">Metode Pembayaran</label>
                <select value={payMethod} onChange={(e) => setPayMethod(e.target.value)} className="w-full bg-slate-50 border border-slate-300 rounded-2xl p-3.5 text-slate-900 text-sm font-bold">
                  <option value="Tunai / Cash">Tunai / Cash (Langsung Korlas)</option>
                  <option value="Transfer Bank (BCA)">Transfer Bank (BCA)</option>
                  <option value="Transfer Bank (Mandiri)">Transfer Bank (Mandiri)</option>
                  <option value="QRIS / Digital">QRIS Digital</option>
                </select>
              </div>

              <div className="flex space-x-3 pt-2">
                <button type="button" onClick={() => setShowPayModal(false)} className="flex-1 py-3 rounded-2xl bg-slate-100 text-slate-700 font-bold">Batal</button>
                <button type="submit" className="flex-1 py-3 rounded-2xl bg-emerald-600 text-white font-bold shadow-md">Simpan Pelunasan</button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
