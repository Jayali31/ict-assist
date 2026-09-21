import React from 'react';
import { Bell, Sparkles, UserCircle } from 'lucide-react';
import { useApp } from '../../context/AppContext';

export const Navbar = () => {
  const { currentUser, currentUserRole, notifications, setActiveTab } = useApp();
  const unreadCount = notifications.filter(n => !n.read).length;

  const getRoleLabel = () => {
    switch (currentUserRole) {
      case 'employee':
        return { label: 'Employee Portal', color: 'bg-blue-100 text-blue-700 border-blue-200' };
      case 'coordinator':
        return { label: 'Helpdesk Coordinator', color: 'bg-purple-100 text-purple-700 border-purple-200' };
      case 'staff':
        return { label: 'ICT Field Staff', color: 'bg-emerald-100 text-emerald-700 border-emerald-200' };
      default:
        return { label: 'Guest', color: 'bg-slate-100 text-slate-700 border-slate-200' };
    }
  };

  const roleInfo = getRoleLabel();

  return (
    <header className="h-16 bg-white border-b border-slate-200/80 px-8 flex items-center justify-between sticky top-0 z-30 shadow-xs">
      <div className="flex items-center space-x-3">
        <h2 className="text-lg font-bold text-slate-800">
          ICT Assist System
        </h2>
        <span className={`text-xs px-2.5 py-0.5 rounded-full font-semibold border ${roleInfo.color}`}>
          {roleInfo.label}
        </span>
      </div>

      <div className="flex items-center space-x-4">
        {/* Notification Bell */}
        <button
          onClick={() => setActiveTab('notifications')}
          className="relative p-2 rounded-xl text-slate-500 hover:text-slate-700 hover:bg-slate-100 transition"
          title="Notifications"
        >
          <Bell size={20} />
          {unreadCount > 0 && (
            <span className="absolute top-1.5 right-1.5 w-2 h-2 bg-rose-500 rounded-full ring-2 ring-white animate-pulse"></span>
          )}
        </button>

        {/* User Profile Capsule */}
        <button 
          onClick={() => setActiveTab('profile')}
          className="flex items-center space-x-3 pl-3 pr-2 py-1.5 rounded-full border border-slate-200 hover:bg-slate-50 transition"
        >
          <div className="text-right hidden sm:block">
            <p className="text-xs font-semibold text-slate-800 leading-tight">
              {currentUser?.name || 'User'}
            </p>
            <p className="text-[11px] text-slate-500 capitalize">
              {currentUser?.roleTitle || currentUserRole}
            </p>
          </div>
          <div className="w-8 h-8 rounded-full bg-blue-600 text-white font-bold text-xs flex items-center justify-center shadow-xs">
            {currentUser?.avatar || 'U'}
          </div>
        </button>
      </div>
    </header>
  );
};
