import React from 'react';
import { useApp } from './context/AppContext';
import { Sidebar } from './components/common/Sidebar';
import { Navbar } from './components/common/Navbar';
import { LoginPage } from './components/shared/LoginPage';
import { NotificationsView } from './components/shared/NotificationsView';
import { ProfileView } from './components/shared/ProfileView';

// Employee components
import { EmployeeDashboard } from './components/employee/EmployeeDashboard';
import { NewRequestForm } from './components/employee/NewRequestForm';
import { TicketStatusView } from './components/employee/TicketStatusView';
import { NotificationSettings } from './components/employee/NotificationSettings';
import { ChangePasswordView } from './components/employee/ChangePasswordView';

// Coordinator components
import { CoordinatorDashboard } from './components/coordinator/CoordinatorDashboard';
import { ReportsView } from './components/coordinator/ReportsView';

// Staff components
import { StaffDashboard } from './components/staff/StaffDashboard';
import { TaskQueueView } from './components/staff/TaskQueueView';

function AppContent() {
  const { currentUserRole, activeTab } = useApp();

  // If user is logged out, show Login Page
  if (!currentUserRole) {
    return <LoginPage />;
  }

  // Render content based on role and active tab
  const renderTabContent = () => {
    // Shared tabs across all roles
    if (activeTab === 'notifications') return <NotificationsView />;
    if (activeTab === 'profile') return <ProfileView />;
    if (activeTab === 'notification-settings') return <NotificationSettings />;
    if (activeTab === 'change-password') return <ChangePasswordView />;

    // Role-specific routing
    if (currentUserRole === 'employee') {
      switch (activeTab) {
        case 'dashboard':
          return <EmployeeDashboard />;
        case 'new-request':
          return <NewRequestForm />;
        case 'tickets':
          return <TicketStatusView />;
        default:
          return <EmployeeDashboard />;
      }
    }

    if (currentUserRole === 'coordinator') {
      switch (activeTab) {
        case 'dashboard':
          return <CoordinatorDashboard />;
        case 'tickets':
          return <TicketStatusView />;
        case 'reports':
          return <ReportsView />;
        default:
          return <CoordinatorDashboard />;
      }
    }

    if (currentUserRole === 'staff') {
      switch (activeTab) {
        case 'dashboard':
          return <StaffDashboard />;
        case 'tasks':
          return <TaskQueueView />;
        default:
          return <StaffDashboard />;
      }
    }

    return <EmployeeDashboard />;
  };

  return (
    <div className="flex min-h-screen bg-slate-100 font-sans">
      {/* Navigation Sidebar */}
      <Sidebar />

      {/* Main Workspace Area */}
      <div className="flex-1 flex flex-col min-w-0 overflow-y-auto">
        <Navbar />
        <main className="flex-1 pb-12">
          {renderTabContent()}
        </main>
      </div>
    </div>
  );
}

export default AppContent;
