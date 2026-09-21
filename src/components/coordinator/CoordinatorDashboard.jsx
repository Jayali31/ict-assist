import React, { useState } from 'react';
import { 
  Inbox, 
  CheckCircle, 
  Clock, 
  AlertCircle, 
  UserCheck, 
  ArrowRight, 
  SlidersHorizontal, 
  Activity,
  Users
} from 'lucide-react';
import { useApp } from '../../context/AppContext';
import { StatusBadge, PriorityBadge } from '../common/Badge';
import { AssignStaffModal } from './AssignStaffModal';

export const CoordinatorDashboard = () => {
  const { tickets, technicians, setActiveTab } = useApp();
  const [assigningTicket, setAssigningTicket] = useState(null);

  const totalCount = tickets.length;
  const resolvedCount = tickets.filter(t => t.status === 'Resolved').length;
  const pendingCount = tickets.filter(t => t.status === 'Pending').length;
  const inProgressCount = tickets.filter(t => t.status === 'In Progress').length;

  const pendingTickets = tickets.filter(t => t.status === 'Pending');

  return (
    <div className="p-8 max-w-6xl mx-auto space-y-8 animate-fadeIn">
      {/* Page Header */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <span className="text-xs font-bold uppercase tracking-wider text-blue-600">
            Operations & Helpdesk Dispatch
          </span>
          <h1 className="text-2xl font-extrabold text-slate-900 mt-1">
            Coordinator Dashboard
          </h1>
          <p className="text-xs text-slate-500 mt-0.5">
            Monitor real-time incoming incidents and dispatch specialized technicians.
          </p>
        </div>
        <button
          onClick={() => setActiveTab('reports')}
          className="self-start sm:self-auto px-4 py-2.5 bg-white border border-slate-200 hover:border-blue-500 rounded-xl text-xs font-semibold text-slate-700 shadow-xs flex items-center space-x-2 transition"
        >
          <Activity size={16} className="text-blue-600" />
          <span>View Performance Reports &rarr;</span>
        </button>
      </div>

      {/* 4 Stat Cards Matching Mockup */}
      <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div className="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
          <div className="flex items-center justify-between">
            <span className="text-xs font-semibold text-slate-500 uppercase">Total Requests</span>
            <div className="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
              <Inbox size={16} />
            </div>
          </div>
          <div className="mt-4">
            <h3 className="text-3xl font-extrabold text-slate-900">{totalCount}</h3>
            <p className="text-[11px] text-blue-600 font-medium mt-1">All logged incidents</p>
          </div>
        </div>

        <div className="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
          <div className="flex items-center justify-between">
            <span className="text-xs font-semibold text-slate-500 uppercase">Resolved</span>
            <div className="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
              <CheckCircle size={16} />
            </div>
          </div>
          <div className="mt-4">
            <h3 className="text-3xl font-extrabold text-slate-900">{resolvedCount}</h3>
            <p className="text-[11px] text-emerald-600 font-medium mt-1">Successfully closed</p>
          </div>
        </div>

        <div className="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
          <div className="flex items-center justify-between">
            <span className="text-xs font-semibold text-slate-500 uppercase">Pending</span>
            <div className="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
              <Clock size={16} />
            </div>
          </div>
          <div className="mt-4">
            <h3 className="text-3xl font-extrabold text-slate-900">{pendingCount}</h3>
            <p className="text-[11px] text-amber-600 font-medium mt-1">Awaiting technician</p>
          </div>
        </div>

        <div className="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
          <div className="flex items-center justify-between">
            <span className="text-xs font-semibold text-slate-500 uppercase">In Progress</span>
            <div className="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
              <Activity size={16} />
            </div>
          </div>
          <div className="mt-4">
            <h3 className="text-3xl font-extrabold text-slate-900">{inProgressCount}</h3>
            <p className="text-[11px] text-purple-600 font-medium mt-1">Technicians active on-site</p>
          </div>
        </div>
      </div>

      {/* Main Grid: Pending Assignments + Field Staff Availability */}
      <div className="grid grid-cols-1 lg:grid-cols-12 gap-8">
        {/* Left: Pending Assignments Queue */}
        <div className="lg:col-span-8 bg-white rounded-3xl border border-slate-200 p-6 shadow-xs">
          <div className="flex items-center justify-between pb-4 border-b border-slate-100">
            <div>
              <h3 className="text-base font-bold text-slate-900">Pending Assignments</h3>
              <p className="text-xs text-slate-500">Tickets awaiting technician dispatch</p>
            </div>
            <span className="text-xs font-semibold px-2.5 py-1 rounded-full bg-amber-100 text-amber-800">
              {pendingTickets.length} Needs Action
            </span>
          </div>

          <div className="divide-y divide-slate-100 mt-2">
            {pendingTickets.length > 0 ? (
              pendingTickets.map((t) => (
                <div key={t.id} className="py-4.5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-slate-50/70 p-3 rounded-2xl transition">
                  <div className="space-y-1">
                    <div className="flex items-center space-x-2">
                      <span className="font-mono text-xs font-bold text-blue-600">#{t.id}</span>
                      <PriorityBadge priority={t.priority} />
                      <span className="text-[11px] font-medium bg-slate-100 text-slate-600 px-2 py-0.5 rounded">
                        {t.category}
                      </span>
                    </div>
                    <h4 className="text-sm font-bold text-slate-800">{t.title}</h4>
                    <p className="text-xs text-slate-500">
                      Branch: {t.branch} &bull; Dept: {t.department} &bull; Created: {t.createdDate}
                    </p>
                  </div>

                  <button
                    onClick={() => setAssigningTicket(t)}
                    className="self-start sm:self-auto bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-4 py-2.5 rounded-xl shadow-md shadow-blue-600/20 flex items-center space-x-1.5 transition active:scale-95"
                  >
                    <UserCheck size={16} />
                    <span>Assign Technician</span>
                  </button>
                </div>
              ))
            ) : (
              <div className="py-12 text-center text-slate-400 text-xs">
                🎉 No pending assignments! All tickets have been assigned to technicians.
              </div>
            )}
          </div>
        </div>

        {/* Right: Field Staff Availability Overview */}
        <div className="lg:col-span-4 bg-white rounded-3xl border border-slate-200 p-6 shadow-xs flex flex-col justify-between">
          <div>
            <div className="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
              <div>
                <h3 className="text-base font-bold text-slate-900">Field Technicians</h3>
                <p className="text-xs text-slate-500">Staff workload & status</p>
              </div>
              <Users size={18} className="text-slate-400" />
            </div>

            <div className="space-y-3">
              {technicians.map((tech) => (
                <div key={tech.id} className="p-3 bg-slate-50 rounded-2xl border border-slate-100 flex items-center justify-between">
                  <div className="flex items-center space-x-3">
                    <div className="w-8 h-8 rounded-full bg-blue-600 text-white text-xs font-bold flex items-center justify-center">
                      {tech.avatar}
                    </div>
                    <div>
                      <p className="text-xs font-bold text-slate-800">{tech.name}</p>
                      <p className="text-[11px] text-slate-500">{tech.specialization}</p>
                    </div>
                  </div>

                  <div className="text-right">
                    <span className={`text-[11px] px-2 py-0.5 rounded-full font-semibold ${
                      tech.status === 'Free' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'
                    }`}>
                      {tech.status}
                    </span>
                    <p className="text-[10px] text-slate-400 mt-1">{tech.assignedCount} Active Tasks</p>
                  </div>
                </div>
              ))}
            </div>
          </div>

          <div className="mt-6 pt-4 border-t border-slate-100 text-center">
            <button
              onClick={() => setActiveTab('tickets')}
              className="text-xs font-semibold text-blue-600 hover:underline inline-flex items-center space-x-1"
            >
              <span>View All System Incident Records</span>
              <ArrowRight size={13} />
            </button>
          </div>
        </div>
      </div>

      {/* Assign Staff Modal */}
      <AssignStaffModal
        isOpen={!!assigningTicket}
        onClose={() => setAssigningTicket(null)}
        ticket={assigningTicket}
      />
    </div>
  );
};
