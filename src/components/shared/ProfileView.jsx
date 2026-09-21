import React, { useState } from 'react';
import { 
  User, 
  History, 
  Bell, 
  Lock, 
  LogOut, 
  ChevronRight, 
  Mail, 
  Phone, 
  Building2, 
  MapPin, 
  Shield 
} from 'lucide-react';
import { useApp } from '../../context/AppContext';
import { Modal } from '../common/Modal';

export const ProfileView = () => {
  const { currentUser, currentUserRole, logout, setActiveTab } = useApp();
  const [showInfoModal, setShowInfoModal] = useState(false);

  return (
    <div className="p-8 max-w-2xl mx-auto space-y-6 animate-fadeIn">
      {/* Profile Hero Card (Mockup Screen 12) */}
      <div className="bg-gradient-to-r from-blue-900 via-blue-800 to-indigo-900 text-white rounded-3xl p-8 shadow-xl shadow-blue-950/20 text-center relative overflow-hidden">
        <div className="relative z-10 flex flex-col items-center">
          <div className="w-20 h-20 rounded-full bg-slate-900/40 border-4 border-white/20 text-white text-2xl font-extrabold flex items-center justify-center shadow-lg mb-3">
            {currentUser?.avatar || 'JD'}
          </div>
          <h2 className="text-xl font-bold text-white tracking-wide">
            {currentUser?.fullName || currentUser?.name || 'M.M.J.C. Deermini'}
          </h2>
          <div className="inline-block mt-1 px-3 py-0.5 rounded-full bg-blue-500/40 text-blue-200 text-xs font-semibold uppercase tracking-wider border border-blue-400/30">
            {currentUser?.roleTitle || currentUserRole}
          </div>
          <p className="text-xs text-blue-200/80 mt-1">
            {currentUser?.department || 'Department Administration'} &bull; {currentUser?.branch || 'Colombo'}
          </p>
        </div>

        <div className="absolute -top-12 -left-12 w-36 h-36 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
      </div>

      {/* Menu Options List */}
      <div className="bg-white rounded-3xl border border-slate-200 divide-y divide-slate-100 shadow-xs overflow-hidden">
        {/* My Information */}
        <button
          onClick={() => setShowInfoModal(true)}
          className="w-full flex items-center justify-between p-4 hover:bg-slate-50 transition text-left"
        >
          <div className="flex items-center space-x-3">
            <div className="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
              <User size={18} />
            </div>
            <div>
              <p className="text-xs font-bold text-slate-800">My Information</p>
              <p className="text-[11px] text-slate-400">View staff ID, branch and contact details</p>
            </div>
          </div>
          <ChevronRight size={18} className="text-slate-400" />
        </button>

        {/* My Ticket History */}
        <button
          onClick={() => setActiveTab('tickets')}
          className="w-full flex items-center justify-between p-4 hover:bg-slate-50 transition text-left"
        >
          <div className="flex items-center space-x-3">
            <div className="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
              <History size={18} />
            </div>
            <div>
              <p className="text-xs font-bold text-slate-800">My Ticket History</p>
              <p className="text-[11px] text-slate-400">Past service requests and resolution archives</p>
            </div>
          </div>
          <ChevronRight size={18} className="text-slate-400" />
        </button>

        {/* Notification Settings */}
        <button
          onClick={() => setActiveTab('notification-settings')}
          className="w-full flex items-center justify-between p-4 hover:bg-slate-50 transition text-left"
        >
          <div className="flex items-center space-x-3">
            <div className="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
              <Bell size={18} />
            </div>
            <div>
              <p className="text-xs font-bold text-slate-800">Notification Settings</p>
              <p className="text-[11px] text-slate-400">Manage email, SMS, and alert preferences</p>
            </div>
          </div>
          <ChevronRight size={18} className="text-slate-400" />
        </button>

        {/* Change Password */}
        <button
          onClick={() => setActiveTab('change-password')}
          className="w-full flex items-center justify-between p-4 hover:bg-slate-50 transition text-left"
        >
          <div className="flex items-center space-x-3">
            <div className="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
              <Lock size={18} />
            </div>
            <div>
              <p className="text-xs font-bold text-slate-800">Change Password</p>
              <p className="text-[11px] text-slate-400">Update your portal authentication credentials</p>
            </div>
          </div>
          <ChevronRight size={18} className="text-slate-400" />
        </button>

        {/* Sign Out */}
        <button
          onClick={logout}
          className="w-full flex items-center justify-between p-4 hover:bg-rose-50 transition text-left group"
        >
          <div className="flex items-center space-x-3">
            <div className="w-9 h-9 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center group-hover:bg-rose-600 group-hover:text-white transition">
              <LogOut size={18} />
            </div>
            <div>
              <p className="text-xs font-bold text-rose-600">Sign Out</p>
              <p className="text-[11px] text-rose-400">Log out of your active session safely</p>
            </div>
          </div>
          <ChevronRight size={18} className="text-rose-400" />
        </button>
      </div>

      {/* My Information Modal */}
      <Modal
        isOpen={showInfoModal}
        onClose={() => setShowInfoModal(false)}
        title="Employee Profile Information"
      >
        <div className="space-y-4 text-xs">
          <div className="flex items-center space-x-3 p-3 bg-slate-50 rounded-2xl border border-slate-100">
            <div className="w-12 h-12 rounded-full bg-blue-600 text-white font-bold flex items-center justify-center text-sm">
              {currentUser?.avatar}
            </div>
            <div>
              <h4 className="font-bold text-slate-800 text-sm">{currentUser?.name}</h4>
              <p className="text-slate-500">{currentUser?.fullName}</p>
            </div>
          </div>

          <div className="space-y-2.5">
            <div className="flex items-center space-x-2 text-slate-600">
              <Mail size={15} className="text-slate-400" />
              <span>Email: <strong>{currentUser?.email}</strong></span>
            </div>
            <div className="flex items-center space-x-2 text-slate-600">
              <Phone size={15} className="text-slate-400" />
              <span>Contact: <strong>{currentUser?.phone}</strong></span>
            </div>
            <div className="flex items-center space-x-2 text-slate-600">
              <Building2 size={15} className="text-slate-400" />
              <span>Department: <strong>{currentUser?.department}</strong></span>
            </div>
            <div className="flex items-center space-x-2 text-slate-600">
              <MapPin size={15} className="text-slate-400" />
              <span>Branch Location: <strong>{currentUser?.branch}</strong></span>
            </div>
            <div className="flex items-center space-x-2 text-slate-600">
              <Shield size={15} className="text-slate-400" />
              <span>Account Role: <strong className="capitalize">{currentUserRole}</strong></span>
            </div>
          </div>

          <div className="pt-3 border-t border-slate-100 flex justify-end">
            <button
              onClick={() => setShowInfoModal(false)}
              className="px-4 py-2 bg-slate-100 text-slate-700 hover:bg-slate-200 rounded-xl text-xs font-semibold transition"
            >
              Close
            </button>
          </div>
        </div>
      </Modal>
    </div>
  );
};
