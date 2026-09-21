import React, { useState } from 'react';
import { Monitor, Lock, Mail, ArrowRight, ShieldCheck, UserCheck, Wrench } from 'lucide-react';
import { useApp } from '../../context/AppContext';

export const LoginPage = () => {
  const { login } = useApp();
  const [selectedRole, setSelectedRole] = useState('employee');
  const [email, setEmail] = useState('a.m.emandi@ictassist.com');
  const [password, setPassword] = useState('••••••••');

  const handleRoleSelect = (role) => {
    setSelectedRole(role);
    if (role === 'employee') setEmail('a.m.emandi@ictassist.com');
    if (role === 'coordinator') setEmail('coordinator@ictassist.com');
    if (role === 'staff') setEmail('ratnasiri@ictassist.com');
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    login(selectedRole);
  };

  return (
    <div className="min-h-screen bg-slate-100 flex items-center justify-center p-4 md:p-8">
      <div className="bg-white w-full max-w-4xl rounded-3xl shadow-2xl overflow-hidden grid grid-cols-1 md:grid-cols-12 border border-slate-200">
        
        {/* Left Hero Panel */}
        <div className="md:col-span-5 bg-gradient-to-br from-slate-900 via-blue-950 to-blue-900 text-white p-8 md:p-10 flex flex-col justify-between relative overflow-hidden">
          {/* Subtle Background Glow */}
          <div className="absolute -top-20 -left-20 w-64 h-64 bg-blue-500/20 rounded-full blur-3xl pointer-events-none"></div>
          <div className="absolute -bottom-20 -right-20 w-64 h-64 bg-blue-400/20 rounded-full blur-3xl pointer-events-none"></div>

          <div>
            <div className="w-14 h-14 rounded-2xl bg-blue-600/90 flex items-center justify-center text-white shadow-xl shadow-blue-500/40 mb-6">
              <Monitor size={30} className="stroke-[2.2]" />
            </div>
            <h1 className="text-3xl font-extrabold tracking-tight text-white mb-2">
              ICT Assist
            </h1>
            <p className="text-blue-200 text-sm leading-relaxed">
              Enterprise IT Service Management & Automated Helpdesk System.
            </p>
          </div>

          <div className="my-8 space-y-4">
            <div className="flex items-start space-x-3 bg-white/10 p-3.5 rounded-xl backdrop-blur-xs border border-white/10">
              <ShieldCheck className="text-blue-400 mt-0.5 shrink-0" size={18} />
              <p className="text-xs text-blue-100">
                <strong>Role-Based Access:</strong> Dedicated portals for Employees, Coordinators, and Field Technicians.
              </p>
            </div>
            <div className="flex items-start space-x-3 bg-white/10 p-3.5 rounded-xl backdrop-blur-xs border border-white/10">
              <Wrench className="text-emerald-400 mt-0.5 shrink-0" size={18} />
              <p className="text-xs text-blue-100">
                <strong>Real-time Tracking:</strong> Live status transitions, technician dispatching, and resolution logging.
              </p>
            </div>
          </div>

          <div className="text-xs text-blue-300/80 font-medium">
            Internal Corporate ICT Support Portal &copy; 2026
          </div>
        </div>

        {/* Right Form Panel */}
        <div className="md:col-span-7 p-8 md:p-12 flex flex-col justify-center">
          <div className="mb-6">
            <h2 className="text-2xl font-bold text-slate-900">Welcome back 👋</h2>
            <p className="text-sm text-slate-500 mt-1">
              Select your user role and sign in to access your dashboard.
            </p>
          </div>

          {/* Role Tabs */}
          <div className="mb-6">
            <label className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">
              Select User Role
            </label>
            <div className="grid grid-cols-3 gap-2 bg-slate-100 p-1.5 rounded-2xl">
              <button
                type="button"
                onClick={() => handleRoleSelect('employee')}
                className={`py-2 px-3 rounded-xl text-xs font-semibold transition ${
                  selectedRole === 'employee'
                    ? 'bg-white text-blue-600 shadow-sm'
                    : 'text-slate-600 hover:text-slate-900'
                }`}
              >
                Employee
              </button>
              <button
                type="button"
                onClick={() => handleRoleSelect('coordinator')}
                className={`py-2 px-3 rounded-xl text-xs font-semibold transition ${
                  selectedRole === 'coordinator'
                    ? 'bg-white text-blue-600 shadow-sm'
                    : 'text-slate-600 hover:text-slate-900'
                }`}
              >
                Coordinator
              </button>
              <button
                type="button"
                onClick={() => handleRoleSelect('staff')}
                className={`py-2 px-3 rounded-xl text-xs font-semibold transition ${
                  selectedRole === 'staff'
                    ? 'bg-white text-blue-600 shadow-sm'
                    : 'text-slate-600 hover:text-slate-900'
                }`}
              >
                Staff (Tech)
              </button>
            </div>
          </div>

          <form onSubmit={handleSubmit} className="space-y-4">
            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1.5">
                Work Email or Staff ID
              </label>
              <div className="relative">
                <Mail className="absolute left-3.5 top-3 text-slate-400" size={18} />
                <input
                  type="text"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  required
                  className="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                  placeholder="name@ictassist.com"
                />
              </div>
            </div>

            <div>
              <div className="flex items-center justify-between mb-1.5">
                <label className="text-xs font-semibold text-slate-700">Password</label>
                <a href="#forgot" className="text-xs text-blue-600 hover:underline">
                  Forgot?
                </a>
              </div>
              <div className="relative">
                <Lock className="absolute left-3.5 top-3 text-slate-400" size={18} />
                <input
                  type="password"
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  required
                  className="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                  placeholder="••••••••"
                />
              </div>
            </div>

            <div className="flex items-center justify-between text-xs text-slate-600 pt-1">
              <label className="flex items-center space-x-2 cursor-pointer">
                <input type="checkbox" defaultChecked className="rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
                <span>Remember this device</span>
              </label>
            </div>

            <button
              type="submit"
              className="w-full mt-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-xl shadow-lg shadow-blue-600/25 flex items-center justify-center space-x-2 transition transform active:scale-[0.99]"
            >
              <span>Sign In as {selectedRole.charAt(0).toUpperCase() + selectedRole.slice(1)}</span>
              <ArrowRight size={18} />
            </button>
          </form>

          {/* Quick Demo Instant Buttons */}
          <div className="mt-8 pt-6 border-t border-slate-100">
            <p className="text-[11px] font-semibold text-slate-400 uppercase tracking-wider text-center mb-3">
              Quick 1-Click Intern Demo Logins
            </p>
            <div className="grid grid-cols-3 gap-2 text-xs">
              <button
                type="button"
                onClick={() => login('employee')}
                className="py-1.5 px-2 bg-slate-50 hover:bg-blue-50 hover:text-blue-600 border border-slate-200 rounded-lg text-slate-600 text-center font-medium transition"
              >
                👤 Employee
              </button>
              <button
                type="button"
                onClick={() => login('coordinator')}
                className="py-1.5 px-2 bg-slate-50 hover:bg-purple-50 hover:text-purple-600 border border-slate-200 rounded-lg text-slate-600 text-center font-medium transition"
              >
                📋 Coordinator
              </button>
              <button
                type="button"
                onClick={() => login('staff')}
                className="py-1.5 px-2 bg-slate-50 hover:bg-emerald-50 hover:text-emerald-600 border border-slate-200 rounded-lg text-slate-600 text-center font-medium transition"
              >
                🛠️ Staff
              </button>
            </div>
          </div>
        </div>

      </div>
    </div>
  );
};
