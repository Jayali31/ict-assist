import React from 'react';
import { Plus, Search, FileText, Bell, History, Clock, ArrowRight, CheckCircle2, AlertCircle } from 'lucide-react';
import { useApp } from '../../context/AppContext';
import { StatusBadge, PriorityBadge } from '../common/Badge';

export const EmployeeDashboard = () => {
  const { currentUser, tickets, setActiveTab } = useApp();

  // Filter tickets for this employee
  const myTickets = tickets.filter(t => t.createdBy === currentUser?.name || t.createdBy === 'A.M. Emandi');
  const activeTicket = myTickets.find(t => t.status === 'In Progress') || myTickets[0];

  const pendingCount = myTickets.filter(t => t.status === 'Pending').length;
  const inProgressCount = myTickets.filter(t => t.status === 'In Progress').length;
  const resolvedCount = myTickets.filter(t => t.status === 'Resolved').length;

  return (
    <div className="p-8 max-w-6xl mx-auto space-y-8 animate-fadeIn">
      {/* Greeting Banner */}
      <div className="relative overflow-hidden rounded-3xl bg-gradient-to-r from-blue-900 via-blue-800 to-indigo-900 text-white p-8 shadow-xl shadow-blue-900/20">
        <div className="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
          <div>
            <span className="text-xs uppercase tracking-widest text-blue-300 font-semibold">
              Employee Helpdesk
            </span>
            <h1 className="text-2xl md:text-3xl font-extrabold tracking-tight mt-1">
              Good morning, {currentUser?.name || 'A.M. Emandi'} 👋
            </h1>
            <p className="text-blue-200 text-sm mt-1 max-w-xl">
              Welcome to the ICT Assist portal. Need technical assistance with hardware, software, or network issues?
            </p>
          </div>
          <button
            onClick={() => setActiveTab('new-request')}
            className="self-start md:self-auto bg-blue-500 hover:bg-blue-400 text-white font-semibold px-5 py-3 rounded-2xl shadow-lg shadow-blue-500/40 flex items-center space-x-2 transition transform active:scale-95"
          >
            <Plus size={20} />
            <span>+ Create New Request</span>
          </button>
        </div>

        {/* Decorative Circles */}
        <div className="absolute -bottom-10 -right-10 w-48 h-48 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
      </div>

      {/* Active Ticket Banner / Ongoing Status */}
      {activeTicket && (
        <div className="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm">
          <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
            <div>
              <div className="flex items-center space-x-2">
                <span className="text-xs font-mono font-bold text-blue-600">#{activeTicket.id}</span>
                <StatusBadge status={activeTicket.status} />
                <PriorityBadge priority={activeTicket.priority} />
              </div>
              <h3 className="text-lg font-bold text-slate-900 mt-1">{activeTicket.title}</h3>
              <p className="text-xs text-slate-500">
                Department: {activeTicket.department} &bull; Created: {activeTicket.createdDate}
              </p>
            </div>
            <button
              onClick={() => setActiveTab('tickets')}
              className="text-xs text-blue-600 font-semibold hover:underline flex items-center space-x-1"
            >
              <span>View Full Details</span>
              <ArrowRight size={14} />
            </button>
          </div>

          <div className="pt-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div className="text-sm text-slate-600">
              <span className="font-semibold text-slate-800">Assigned Technician:</span>{' '}
              {activeTicket.assignedTo ? (
                <span className="inline-flex items-center text-blue-700 font-medium bg-blue-50 px-2 py-0.5 rounded-md text-xs">
                  {activeTicket.assignedTo}
                </span>
              ) : (
                <span className="text-slate-400 italic">Awaiting Coordinator Assignment</span>
              )}
            </div>
            {activeTicket.progressNotes && activeTicket.progressNotes.length > 0 && (
              <div className="text-xs text-slate-500 bg-slate-50 px-3 py-2 rounded-xl border border-slate-100 flex items-center space-x-2">
                <Clock size={14} className="text-blue-500" />
                <span>Latest update: {activeTicket.progressNotes[activeTicket.progressNotes.length - 1]}</span>
              </div>
            )}
          </div>
        </div>
      )}

      {/* Quick Action Cards (4 Grid Matching Mockup) */}
      <div>
        <h2 className="text-sm font-bold uppercase tracking-wider text-slate-500 mb-4">
          Quick Actions & Service Hub
        </h2>
        <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
          <button
            onClick={() => setActiveTab('new-request')}
            className="bg-white p-5 rounded-2xl border border-slate-200 hover:border-blue-500 hover:shadow-md transition text-left group"
          >
            <div className="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center mb-3 group-hover:bg-blue-600 group-hover:text-white transition">
              <Plus size={20} />
            </div>
            <h4 className="text-sm font-bold text-slate-800">New Request</h4>
            <p className="text-xs text-slate-500 mt-0.5">Submit incident or repair</p>
          </button>

          <button
            onClick={() => setActiveTab('tickets')}
            className="bg-white p-5 rounded-2xl border border-slate-200 hover:border-blue-500 hover:shadow-md transition text-left group"
          >
            <div className="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center mb-3 group-hover:bg-amber-600 group-hover:text-white transition">
              <Clock size={20} />
            </div>
            <h4 className="text-sm font-bold text-slate-800">Track Request</h4>
            <p className="text-xs text-slate-500 mt-0.5">{inProgressCount} in progress, {pendingCount} pending</p>
          </button>

          <button
            onClick={() => setActiveTab('notifications')}
            className="bg-white p-5 rounded-2xl border border-slate-200 hover:border-blue-500 hover:shadow-md transition text-left group"
          >
            <div className="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center mb-3 group-hover:bg-purple-600 group-hover:text-white transition">
              <Bell size={20} />
            </div>
            <h4 className="text-sm font-bold text-slate-800">Notifications</h4>
            <p className="text-xs text-slate-500 mt-0.5">Live dispatcher alerts</p>
          </button>

          <button
            onClick={() => setActiveTab('tickets')}
            className="bg-white p-5 rounded-2xl border border-slate-200 hover:border-blue-500 hover:shadow-md transition text-left group"
          >
            <div className="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-3 group-hover:bg-emerald-600 group-hover:text-white transition">
              <History size={20} />
            </div>
            <h4 className="text-sm font-bold text-slate-800">My History</h4>
            <p className="text-xs text-slate-500 mt-0.5">{resolvedCount} resolved tickets</p>
          </button>
        </div>
      </div>

      {/* Recent Tickets Table Preview */}
      <div className="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm">
        <div className="flex items-center justify-between mb-5">
          <div>
            <h3 className="text-base font-bold text-slate-800">Recent Requests</h3>
            <p className="text-xs text-slate-500">Your submitted tickets across branches</p>
          </div>
          <button
            onClick={() => setActiveTab('tickets')}
            className="text-xs font-semibold text-blue-600 hover:underline flex items-center space-x-1"
          >
            <span>View All</span>
            <ArrowRight size={14} />
          </button>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full text-left text-xs">
            <thead>
              <tr className="border-b border-slate-100 text-slate-400 uppercase tracking-wider">
                <th className="pb-3 font-semibold">Ticket ID</th>
                <th className="pb-3 font-semibold">Issue Title</th>
                <th className="pb-3 font-semibold">Category</th>
                <th className="pb-3 font-semibold">Priority</th>
                <th className="pb-3 font-semibold">Status</th>
                <th className="pb-3 font-semibold text-right">Action</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {myTickets.slice(0, 4).map((t) => (
                <tr key={t.id} className="hover:bg-slate-50/80 transition">
                  <td className="py-3.5 font-mono font-bold text-blue-600">#{t.id}</td>
                  <td className="py-3.5 font-semibold text-slate-800">{t.title}</td>
                  <td className="py-3.5 text-slate-600">{t.category}</td>
                  <td className="py-3.5"><PriorityBadge priority={t.priority} /></td>
                  <td className="py-3.5"><StatusBadge status={t.status} /></td>
                  <td className="py-3.5 text-right">
                    <button
                      onClick={() => setActiveTab('tickets')}
                      className="text-blue-600 font-semibold hover:text-blue-800 text-xs"
                    >
                      Details &rarr;
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
};
