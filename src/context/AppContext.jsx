import React, { createContext, useContext, useState, useEffect } from 'react';
import {
  initialUsers,
  initialTickets,
  initialTechnicians,
  initialNotifications,
} from '../data/initialData';

const AppContext = createContext();

export const AppProvider = ({ children }) => {
  // Load state from localStorage or fallback to defaults
  const [currentUserRole, setCurrentUserRole] = useState(() => {
    return localStorage.getItem('ict_current_role') || 'employee';
  });

  const [tickets, setTickets] = useState(() => {
    const saved = localStorage.getItem('ict_tickets');
    return saved ? JSON.parse(saved) : initialTickets;
  });

  const [technicians, setTechnicians] = useState(() => {
    const saved = localStorage.getItem('ict_technicians');
    return saved ? JSON.parse(saved) : initialTechnicians;
  });

  const [notifications, setNotifications] = useState(() => {
    const saved = localStorage.getItem('ict_notifications');
    return saved ? JSON.parse(saved) : initialNotifications;
  });

  const [activeTab, setActiveTab] = useState('dashboard');
  const [selectedTicketForAssign, setSelectedTicketForAssign] = useState(null);

  // Sync to localStorage
  useEffect(() => {
    localStorage.setItem('ict_current_role', currentUserRole);
  }, [currentUserRole]);

  useEffect(() => {
    localStorage.setItem('ict_tickets', JSON.stringify(tickets));
  }, [tickets]);

  useEffect(() => {
    localStorage.setItem('ict_technicians', JSON.stringify(technicians));
  }, [technicians]);

  useEffect(() => {
    localStorage.setItem('ict_notifications', JSON.stringify(notifications));
  }, [notifications]);

  // Current active user object
  const currentUser = currentUserRole ? initialUsers[currentUserRole] : null;

  // Actions
  const login = (role) => {
    setCurrentUserRole(role);
    setActiveTab('dashboard');
  };

  const logout = () => {
    setCurrentUserRole(null);
  };

  const switchRole = (role) => {
    setCurrentUserRole(role);
    setActiveTab('dashboard');
  };

  const createTicket = (ticketData) => {
    const nextIdNumber = 2027 + tickets.length;
    const newTicket = {
      id: `TX-${nextIdNumber}`,
      title: ticketData.title,
      category: ticketData.category,
      priority: ticketData.priority,
      department: ticketData.department || currentUser?.department || 'General Admin',
      branch: ticketData.branch || currentUser?.branch || 'Colombo Main Office',
      createdDate: new Date().toISOString().split('T')[0],
      status: 'Pending',
      assignedTo: null,
      assignedTechId: null,
      createdBy: currentUser?.name || 'A.M. Emandi',
      description: ticketData.description,
      attachment: ticketData.attachment || null,
      progressNotes: ['Ticket submitted by ' + (currentUser?.name || 'Employee')]
    };

    setTickets([newTicket, ...tickets]);

    // Push notification
    const newNotif = {
      id: `notif-${Date.now()}`,
      title: `New Ticket Submitted: #${newTicket.id}`,
      subtitle: `${newTicket.title} (${newTicket.priority} Priority)`,
      time: 'Just now',
      group: 'Today',
      type: newTicket.priority === 'High' ? 'warning' : 'info',
      read: false
    };
    setNotifications([newNotif, ...notifications]);

    return newTicket;
  };

  const assignTicket = (ticketId, techId) => {
    const tech = technicians.find(t => t.id === techId);
    if (!tech) return;

    setTickets(prev =>
      prev.map(ticket => {
        if (ticket.id === ticketId) {
          const notes = ticket.progressNotes || [];
          return {
            ...ticket,
            status: 'In Progress',
            assignedTo: tech.name,
            assignedTechId: tech.id,
            progressNotes: [...notes, `Assigned to technician ${tech.name}`]
          };
        }
        return ticket;
      })
    );

    // Update technician assigned count
    setTechnicians(prev =>
      prev.map(t => {
        if (t.id === techId) {
          return { ...t, assignedCount: t.assignedCount + 1 };
        }
        return t;
      })
    );

    // Add notification
    const newNotif = {
      id: `notif-${Date.now()}`,
      title: `Technician assigned to #${ticketId}`,
      subtitle: `${tech.name} has been assigned to attend to this request.`,
      time: 'Just now',
      group: 'Today',
      type: 'info',
      read: false
    };
    setNotifications([newNotif, ...notifications]);
  };

  const updateTicketStatus = (ticketId, newStatus, note = '') => {
    setTickets(prev =>
      prev.map(ticket => {
        if (ticket.id === ticketId) {
          const notes = ticket.progressNotes || [];
          const updatedNotes = note ? [...notes, note] : notes;
          return {
            ...ticket,
            status: newStatus,
            progressNotes: updatedNotes
          };
        }
        return ticket;
      })
    );

    if (newStatus === 'Resolved') {
      const target = tickets.find(t => t.id === ticketId);
      if (target && target.assignedTechId) {
        setTechnicians(prev =>
          prev.map(t => {
            if (t.id === target.assignedTechId) {
              return {
                ...t,
                resolvedCount: t.resolvedCount + 1,
                assignedCount: Math.max(0, t.assignedCount - 1)
              };
            }
            return t;
          })
        );
      }
    }

    // Add notification
    const newNotif = {
      id: `notif-${Date.now()}`,
      title: `Status update on #${ticketId}: ${newStatus}`,
      subtitle: note || `Ticket #${ticketId} is now marked as ${newStatus}.`,
      time: 'Just now',
      group: 'Today',
      type: newStatus === 'Resolved' ? 'success' : 'info',
      read: false
    };
    setNotifications([newNotif, ...notifications]);
  };

  const updateStaffStatus = (newStatus) => {
    if (currentUserRole === 'staff') {
      setTechnicians(prev =>
        prev.map(t => {
          if (t.id === currentUser?.id) {
            return { ...t, status: newStatus === 'Available' ? 'Free' : 'Busy' };
          }
          return t;
        })
      );
    }
  };

  const markAllNotificationsRead = () => {
    setNotifications(prev => prev.map(n => ({ ...n, read: true })));
  };

  const resetToDefaults = () => {
    setTickets(initialTickets);
    setTechnicians(initialTechnicians);
    setNotifications(initialNotifications);
    setCurrentUserRole('employee');
    setActiveTab('dashboard');
    localStorage.clear();
  };

  return (
    <AppContext.Provider
      value={{
        currentUserRole,
        currentUser,
        tickets,
        technicians,
        notifications,
        activeTab,
        selectedTicketForAssign,
        setSelectedTicketForAssign,
        setActiveTab,
        login,
        logout,
        switchRole,
        createTicket,
        assignTicket,
        updateTicketStatus,
        updateStaffStatus,
        markAllNotificationsRead,
        resetToDefaults
      }}
    >
      {children}
    </AppContext.Provider>
  );
};

export const useApp = () => {
  const context = useContext(AppContext);
  if (!context) {
    throw new Error('useApp must be used within an AppProvider');
  }
  return context;
};
