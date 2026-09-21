import React from 'react';
import { 
  LayoutDashboard, 
  PlusCircle, 
  Clock, 
  UserCheck, 
  BarChart3, 
  CheckSquare, 
  Bell, 
  User, 
  LogOut, 
  Monitor,
  Repeat
} from 'lucide-react';
import { useApp } from '../../context/AppContext';

export const Sidebar = () => {
  const { currentUserRole, activeTab, setActiveTab, logout, switchRole, notifications } = useApp();

  const unreadCount = notifications.filter(n => !n.read).length;

  const getMenuItems = () => {
    switch (currentUserRole) {
      case 'employee':
        return [
          { id: 'dashboard', label: 'Dashboard', icon: LayoutDashboard },
          { id: 'new-request', label: 'New Request', icon: PlusCircle },
          { id: 'tickets', label: 'Ticket Status', icon: Clock },
          { id: 'notifications', label: 'Notifications', icon: Bell, badge: unreadCount },
          { id: 'profile', label: 'Profile', icon: User },
        ];
      case 'coordinator':
        return [
          { id: 'dashboard', label: 'Dashboard', icon: LayoutDashboard },
          { id: 'tickets', label: 'Ticket Status', icon: Clock },
          { id: 'reports', label: 'Reports', icon: BarChart3 },
          { id: 'notifications', label: 'Notifications', icon: Bell, badge: unreadCount },
          { id: 'profile', label: 'Profile', icon: User },
        ];
      case 'staff':
        return [
          { id: 'dashboard', label: 'Staff Task', icon: LayoutDashboard },
          { id: 'tasks', label: 'Task Queue', icon: CheckSquare },
          { id: 'notifications', label: 'Notifications', icon: Bell, badge: unreadCount },
          { id: 'profile', label: 'Profile', icon: User },
        ];
      default:
        return [];
    }
  };

  const menuItems = getMenuItems();

  return (
    <aside className="w-64 bg-[#111827] text-slate-300 min-h-screen flex flex-col justify-between border-r border-slate-800 shadow-xl select-none">
      <div>
        {/* Brand Header */}
        <div className="p-6 border-b border-slate-800/80 flex items-center space-x-3">
          <div className="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-700 to-blue-500 flex items-center justify-center text-white shadow-lg shadow-blue-500/30">
            <Monitor size={22} className="stroke-[2.2]" />
          </div>
          <div>
            <h1 className="text-lg font-bold text-white tracking-wide">ICT Assist</h1>
            <p className="text-xs text-slate-400 capitalize font-medium">{currentUserRole} Portal</p>
          </div>
        </div>

        {/* Navigation Items */}
        <nav className="p-4 space-y-1.5">
          <p className="text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 mb-2">
            Main Menu
          </p>
          {menuItems.map((item) => {
            const Icon = item.icon;
            const isActive = activeTab === item.id;
            return (
              <button
                key={item.id}
                onClick={() => setActiveTab(item.id)}
                className={`w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all duration-150 ${
                  isActive
                    ? 'bg-blue-600 text-white shadow-md shadow-blue-600/25 font-semibold'
                    : 'text-slate-300 hover:text-white hover:bg-slate-800/60'
                }`}
              >
                <div className="flex items-center space-x-3">
                  <Icon size={18} className={isActive ? 'text-white' : 'text-slate-400'} />
                  <span>{item.label}</span>
                </div>
                {item.badge > 0 && (
                  <span className={`text-[11px] px-2 py-0.5 rounded-full font-bold ${
                    isActive ? 'bg-white text-blue-600' : 'bg-blue-600 text-white'
                  }`}>
                    {item.badge}
                  </span>
                )}
              </button>
            );
          })}
        </nav>
      </div>

      {/* Role Switcher & Bottom Actions */}
      <div className="p-4 border-t border-slate-800/80 space-y-3">
        {/* Quick Intern Role Switcher */}
        <div className="bg-slate-900/90 rounded-xl p-3 border border-slate-800">
          <div className="flex items-center justify-between text-xs text-slate-400 mb-2 font-medium">
            <span className="flex items-center gap-1.5 text-slate-300">
              <Repeat size={13} className="text-blue-400" /> Switch Role:
            </span>
          </div>
          <div className="grid grid-cols-3 gap-1">
            <button
              onClick={() => switchRole('employee')}
              className={`py-1 text-[11px] rounded font-medium transition ${
                currentUserRole === 'employee'
                  ? 'bg-blue-600 text-white font-semibold'
                  : 'bg-slate-800 text-slate-400 hover:text-white'
              }`}
            >
              Employee
            </button>
            <button
              onClick={() => switchRole('coordinator')}
              className={`py-1 text-[11px] rounded font-medium transition ${
                currentUserRole === 'coordinator'
                  ? 'bg-blue-600 text-white font-semibold'
                  : 'bg-slate-800 text-slate-400 hover:text-white'
              }`}
            >
              Coord
            </button>
            <button
              onClick={() => switchRole('staff')}
              className={`py-1 text-[11px] rounded font-medium transition ${
                currentUserRole === 'staff'
                  ? 'bg-blue-600 text-white font-semibold'
                  : 'bg-slate-800 text-slate-400 hover:text-white'
              }`}
            >
              Staff
            </button>
          </div>
        </div>

        {/* Logout */}
        <button
          onClick={logout}
          className="w-full flex items-center justify-center space-x-2 px-3 py-2 rounded-xl text-xs font-semibold text-rose-400 hover:bg-rose-500/10 transition border border-rose-500/20"
        >
          <LogOut size={15} />
          <span>Sign Out</span>
        </button>
      </div>
    </aside>
  );
};
