export const initialUsers = {
  employee: {
    id: 'usr-emp-01',
    name: 'A.M. Emandi',
    fullName: 'M.M.J.C. Deermini',
    email: 'a.m.emandi@ictassist.com',
    role: 'employee',
    roleTitle: 'Employee',
    department: 'Accounts & Finance',
    branch: 'Colombo Main Office',
    phone: '+94 77 123 4567',
    avatar: 'AE'
  },
  coordinator: {
    id: 'usr-coord-01',
    name: 'Sarah Jayasinghe',
    fullName: 'Sarah Jayasinghe',
    email: 'coordinator@ictassist.com',
    role: 'coordinator',
    roleTitle: 'Helpdesk Coordinator',
    department: 'ICT Support & Dispatch',
    branch: 'Headquarters',
    phone: '+94 71 987 6543',
    avatar: 'SJ'
  },
  staff: {
    id: 'tech-01',
    name: 'K.P. Ratnasiri',
    fullName: 'K.P. Ratnasiri',
    email: 'ratnasiri@ictassist.com',
    role: 'staff',
    roleTitle: 'Hardware Technician',
    department: 'Hardware Maintenance',
    branch: 'Field Services',
    specialization: 'Hardware',
    phone: '+94 70 555 1234',
    status: 'Available', // Available | Busy
    avatar: 'KR'
  }
};

export const initialTechnicians = [
  {
    id: 'tech-01',
    name: 'K.P. Ratnasiri',
    specialization: 'Hardware',
    status: 'Free',
    assignedCount: 4,
    resolvedCount: 56,
    avatar: 'KR'
  },
  {
    id: 'tech-02',
    name: 'A.S. Fernando',
    specialization: 'Network',
    status: 'Free',
    assignedCount: 2,
    resolvedCount: 42,
    avatar: 'AF'
  },
  {
    id: 'tech-03',
    name: 'M.E. Perera',
    specialization: 'Software',
    status: 'Free',
    assignedCount: 3,
    resolvedCount: 38,
    avatar: 'MP'
  },
  {
    id: 'tech-04',
    name: 'N.L. Silva',
    specialization: 'Telephone',
    status: 'Busy',
    assignedCount: 5,
    resolvedCount: 31,
    avatar: 'NS'
  }
];

export const initialTickets = [
  {
    id: 'TX-2024',
    title: 'Printer not working',
    category: 'Hardware',
    priority: 'High',
    department: 'Telecom & Admin',
    branch: 'Colombo Main Office',
    createdDate: '2024-09-18',
    status: 'In Progress', // Pending | In Progress | Resolved
    assignedTo: 'K.P. Ratnasiri',
    assignedTechId: 'tech-01',
    createdBy: 'A.M. Emandi',
    description: 'Paper jam in main office network laser printer. Continuous error 50.4 displayed on control panel.',
    attachment: 'printer_error_screen.jpg',
    progressNotes: ['Assigned to K.P. Ratnasiri', 'Inspected paper feed rollers', 'Awaiting spare roller assembly']
  },
  {
    id: 'TX-2025',
    title: 'Server room AC filters maintenance',
    category: 'Hardware',
    priority: 'Medium',
    department: 'Server Room Admin',
    branch: 'Colombo Main Office',
    createdDate: '2024-09-19',
    status: 'Pending',
    assignedTo: null,
    assignedTechId: null,
    createdBy: 'A.M. Emandi',
    description: 'Quarterly cleaning and replacement of high-density air filters inside primary rack enclosures.',
    attachment: null,
    progressNotes: []
  },
  {
    id: 'TX-2026',
    title: 'VPN Connection failing across subnet',
    category: 'Network',
    priority: 'High',
    department: 'Finance',
    branch: 'Kandy Branch',
    createdDate: '2024-09-20',
    status: 'Pending',
    assignedTo: null,
    assignedTechId: null,
    createdBy: 'Kamal Perera',
    description: 'Remote staff unable to establish IPsec VPN tunnel with ERP database. Gateway timeout.',
    attachment: null,
    progressNotes: []
  },
  {
    id: 'TX-2021',
    title: 'Windows 11 update crash & Office suite',
    category: 'Software',
    priority: 'Low',
    department: 'Human Resources',
    branch: 'Colombo Main Office',
    createdDate: '2024-09-15',
    status: 'Resolved',
    assignedTo: 'M.E. Perera',
    assignedTechId: 'tech-03',
    createdBy: 'A.M. Emandi',
    description: 'Outlook and Excel crashed continuously following automated security update KB503412.',
    attachment: null,
    progressNotes: ['Rolled back faulty driver', 'Rebuilt Outlook cache', 'Issue confirmed fixed']
  },
  {
    id: 'TX-2020',
    title: 'IP Phone extension 402 silent dial tone',
    category: 'Telephone',
    priority: 'Medium',
    department: 'Customer Service',
    branch: 'Galle Branch',
    createdDate: '2024-09-12',
    status: 'Resolved',
    assignedTo: 'N.L. Silva',
    assignedTechId: 'tech-04',
    createdBy: 'Sunil Silva',
    description: 'VoIP phone screen functions properly but handset receiver produces no dial tone.',
    attachment: null,
    progressNotes: ['Replaced RJ9 spiral cord', 'Tested audio loopback ok']
  }
];

export const initialNotifications = [
  {
    id: 'notif-1',
    title: 'Technician assigned to your ticket #TX-2024',
    subtitle: 'K.P. Ratnasiri will visit your branch today.',
    time: '10:15 AM',
    group: 'Today',
    type: 'info',
    read: false
  },
  {
    id: 'notif-2',
    title: 'Work started on ticket #TX-2024',
    subtitle: 'Technician is on-site at Colombo Main Office.',
    time: '10:45 AM',
    group: 'Today',
    type: 'success',
    read: false
  },
  {
    id: 'notif-3',
    title: 'High priority ticket #TX-2026 submitted',
    subtitle: 'Awaiting coordinator assignment for Kandy Branch.',
    time: '11:00 AM',
    group: 'Today',
    type: 'warning',
    read: false
  },
  {
    id: 'notif-4',
    title: 'Ticket #TX-2021 has been resolved and closed',
    subtitle: 'Windows update issue resolved by M.E. Perera.',
    time: 'Yesterday, 4:30 PM',
    group: 'Yesterday',
    type: 'success',
    read: true
  }
];
