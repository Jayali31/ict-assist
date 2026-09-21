// Shared Data & State Management for Multi-Page ICT Assist
const defaultTickets = [
  { id: 'TX-2024', title: 'Printer not working', category: 'Hardware', priority: 'High', department: 'Telecom & Admin', branch: 'Colombo Main Office', status: 'In Progress', assignedTo: 'K.P. Ratnasiri', createdDate: '2024-09-18', description: 'Paper jam in main office network laser printer. Continuous error 50.4.' },
  { id: 'TX-2025', title: 'Server room AC filters maintenance', category: 'Hardware', priority: 'Medium', department: 'Admin', branch: 'Colombo Main Office', status: 'Pending', assignedTo: null, createdDate: '2024-09-19', description: 'Quarterly cleaning and replacement of high-density air filters.' },
  { id: 'TX-2026', title: 'VPN Connection failing across subnet', category: 'Network', priority: 'High', department: 'Finance', branch: 'Kandy Branch', status: 'Pending', assignedTo: null, createdDate: '2024-09-20', description: 'Remote staff unable to establish IPsec VPN tunnel with ERP database.' },
  { id: 'TX-2021', title: 'Windows 11 update crash & Office suite', category: 'Software', priority: 'Low', department: 'HR', branch: 'Colombo Main Office', status: 'Resolved', assignedTo: 'M.E. Perera', createdDate: '2024-09-15', description: 'Outlook and Excel crashed following update. System restored.' }
];

function getTickets() {
  const saved = localStorage.getItem('ict_tickets_db');
  if (!saved) {
    localStorage.setItem('ict_tickets_db', JSON.stringify(defaultTickets));
    return defaultTickets;
  }
  return JSON.parse(saved);
}

function saveTickets(tickets) {
  localStorage.setItem('ict_tickets_db', JSON.stringify(tickets));
}

function addTicket(newTicket) {
  const list = getTickets();
  list.unshift(newTicket);
  saveTickets(list);
}

function updateTicket(id, updates) {
  const list = getTickets().map(t => t.id === id ? { ...t, ...updates } : t);
  saveTickets(list);
}
