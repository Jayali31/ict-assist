import React, { useState } from 'react';
import { 
  Wrench, 
  CheckCircle, 
  Clock, 
  AlertTriangle, 
  ArrowRight, 
  CheckSquare, 
  MessageSquare,
  FileCheck,
  ToggleLeft,
  ToggleRight
} from 'lucide-react';
import { useApp } from '../../context/AppContext';
import { StatusBadge, PriorityBadge } from '../common/Badge';
import { Modal } from '../common/Modal';

export const StaffDashboard = () => {
  const { currentUser, tickets, updateTicketStatus, updateStaffStatus } = useApp();
  const [selectedTaskForUpdate, setSelectedTaskForUpdate] = useState(null);
  const [updateStatusVal, setUpdateStatusVal] = useState('Resolved');
  const [progressNote, setProgressNote] = useState('');
  const [staffAvailability, setStaffAvailability] = useState('Available');

  // Filter tasks assigned to this technician
  const myAssignedTasks = tickets.filter(
    t => t.assignedTo === currentUser?.name || t.assignedTechId === currentUser?.id || t.assignedTo === 'K.P. Ratnasiri'
  );

  const activeTask = myAssignedTasks.find(t => t.status === 'In Progress') || myAssignedTasks[0];
  const pendingQueue = myAssignedTasks.filter(t => t.id !== activeTask?.id);

  const handleToggleAvailability = () => {
    const nextStatus = staffAvailability === 'Available' ? 'Busy' : 'Available';
    setStaffAvailability(nextStatus);
    updateStaffStatus(nextStatus);
  };

  const handleSaveProgress = (e) => {
    e.preventDefault();
    if (!selectedTaskForUpdate) return;

    updateTicketStatus(
      selectedTaskForUpdate.id,
      updateStatusVal,
      progressNote || `Status updated to ${updateStatusVal} by ${currentUser?.name}`
    );

    setSelectedTaskForUpdate(null);
    setProgressNote('');
  };

  return (
    <div className="p-8 max-w-6xl mx-auto space-y-8 animate-fadeIn">
      {/* Staff Greeting Header */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-3xl border border-slate-200 shadow-xs">
        <div>
          <span className="text-xs font-bold uppercase tracking-wider text-emerald-600">
            Field Technician Console
          </span>
          <h1 className="text-2xl font-extrabold text-slate-900 mt-1">
            Good morning, {currentUser?.name || 'K.P. Ratnasiri'} 🛠️
          </h1>
          <p className="text-xs text-slate-500 mt-0.5">
            Specialization: <strong>Hardware & Infrastructure</strong> &bull; Colombo Field Zone
          </p>
        </div>

        {/* Availability Toggle */}
        <div className="flex items-center space-x-3 bg-slate-50 px-4 py-2.5 rounded-2xl border border-slate-200">
          <span className="text-xs font-semibold text-slate-700">Status:</span>
          <button
            onClick={handleToggleAvailability}
            className={`flex items-center space-x-1.5 px-3 py-1 rounded-full text-xs font-bold transition ${
              staffAvailability === 'Available'
                ? 'bg-emerald-100 text-emerald-700 border border-emerald-300'
                : 'bg-rose-100 text-rose-700 border border-rose-300'
            }`}
          >
            <span className={`w-2 h-2 rounded-full ${
              staffAvailability === 'Available' ? 'bg-emerald-500 animate-ping' : 'bg-rose-500'
            }`}></span>
            <span>{staffAvailability}</span>
          </button>
        </div>
      </div>

      {/* Staff Metric Cards (Mockup Screen 10) */}
      <div className="grid grid-cols-3 gap-4">
        <div className="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs text-center">
          <span className="text-xs font-semibold text-slate-500 uppercase">Assigned</span>
          <h3 className="text-3xl font-extrabold text-blue-600 mt-2">{myAssignedTasks.length}</h3>
          <p className="text-[11px] text-slate-400 mt-1">Total jobs allocated</p>
        </div>

        <div className="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs text-center">
          <span className="text-xs font-semibold text-slate-500 uppercase">Active</span>
          <h3 className="text-3xl font-extrabold text-amber-500 mt-2">
            {myAssignedTasks.filter(t => t.status === 'In Progress').length}
          </h3>
          <p className="text-[11px] text-slate-400 mt-1">Currently troubleshooting</p>
        </div>

        <div className="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs text-center">
          <span className="text-xs font-semibold text-slate-500 uppercase">Completed</span>
          <h3 className="text-3xl font-extrabold text-emerald-600 mt-2">
            {myAssignedTasks.filter(t => t.status === 'Resolved').length + 29}
          </h3>
          <p className="text-[11px] text-slate-400 mt-1">Closed successfully</p>
        </div>
      </div>

      {/* ACTIVE TASK CARD (Screen 10 Primary Focus) */}
      {activeTask ? (
        <div className="bg-gradient-to-br from-blue-900 via-slate-900 to-indigo-950 text-white rounded-3xl p-7 shadow-xl shadow-blue-950/20 border border-blue-800/40 relative overflow-hidden">
          <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-white/10">
            <div>
              <span className="inline-block text-[11px] font-bold uppercase tracking-widest bg-blue-500/30 text-blue-300 px-3 py-0.5 rounded-full border border-blue-400/30">
                ACTIVE TASK
              </span>
              <h3 className="text-xl font-bold text-white mt-2 flex items-center gap-2">
                #{activeTask.id}: {activeTask.title}
              </h3>
              <p className="text-xs text-blue-200 mt-1">
                Branch: <strong>{activeTask.branch}</strong> &bull; Dept: <strong>{activeTask.department}</strong>
              </p>
            </div>
            <div className="flex items-center space-x-2">
              <PriorityBadge priority={activeTask.priority} />
              <StatusBadge status={activeTask.status} />
            </div>
          </div>

          <div className="py-4 text-xs text-blue-100 leading-relaxed max-w-2xl">
            {activeTask.description}
          </div>

          <div className="pt-4 border-t border-white/10 flex flex-wrap items-center justify-between gap-4">
            <div className="text-xs text-blue-300">
              Submitted by: <strong>{activeTask.createdBy}</strong> &bull; Date: {activeTask.createdDate}
            </div>
            <div className="flex items-center space-x-3">
              <button
                onClick={() => {
                  setSelectedTaskForUpdate(activeTask);
                  setUpdateStatusVal(activeTask.status === 'Resolved' ? 'In Progress' : 'Resolved');
                }}
                className="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold shadow-lg shadow-blue-600/40 transition active:scale-95"
              >
                Update Progress
              </button>
            </div>
          </div>
        </div>
      ) : (
        <div className="bg-white rounded-3xl p-8 text-center text-slate-400 border border-slate-200">
          No active tasks currently assigned. Check your task queue below.
        </div>
      )}

      {/* TASK QUEUE */}
      <div className="bg-white rounded-3xl border border-slate-200 p-6 shadow-xs">
        <div className="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
          <div>
            <h3 className="text-base font-bold text-slate-900">Task Queue</h3>
            <p className="text-xs text-slate-500">Upcoming tickets scheduled for inspection</p>
          </div>
          <span className="text-xs font-semibold bg-slate-100 px-3 py-1 rounded-full text-slate-600">
            {pendingQueue.length} In Queue
          </span>
        </div>

        <div className="divide-y divide-slate-100">
          {pendingQueue.length > 0 ? (
            pendingQueue.map((t) => (
              <div key={t.id} className="py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-slate-50/70 p-3 rounded-2xl transition">
                <div className="space-y-1">
                  <div className="flex items-center space-x-2">
                    <span className="font-mono text-xs font-bold text-blue-600">#{t.id}</span>
                    <PriorityBadge priority={t.priority} />
                    <StatusBadge status={t.status} />
                  </div>
                  <h4 className="text-sm font-bold text-slate-800">{t.title}</h4>
                  <p className="text-xs text-slate-500">
                    {t.branch} &bull; {t.department} &bull; Created: {t.createdDate}
                  </p>
                </div>

                <div className="flex items-center space-x-2">
                  <button
                    onClick={() => {
                      setSelectedTaskForUpdate(t);
                      setUpdateStatusVal(t.status === 'Resolved' ? 'In Progress' : 'Resolved');
                    }}
                    className="px-4 py-2 bg-slate-100 hover:bg-blue-50 hover:text-blue-600 rounded-xl text-xs font-semibold text-slate-700 transition"
                  >
                    Update
                  </button>
                </div>
              </div>
            ))
          ) : (
            <div className="py-8 text-center text-xs text-slate-400">
              No further tickets queued in your zone.
            </div>
          )}
        </div>
      </div>

      {/* Update Progress Modal */}
      {selectedTaskForUpdate && (
        <Modal
          isOpen={!!selectedTaskForUpdate}
          onClose={() => setSelectedTaskForUpdate(null)}
          title={`Update Progress: #${selectedTaskForUpdate.id}`}
        >
          <form onSubmit={handleSaveProgress} className="space-y-4">
            <div>
              <label className="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                New Ticket Status
              </label>
              <select
                value={updateStatusVal}
                onChange={(e) => setUpdateStatusVal(e.target.value)}
                className="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
              >
                <option value="In Progress">In Progress (Work Ongoing)</option>
                <option value="Resolved">Resolved (Problem Solved & Tested)</option>
                <option value="Pending">Pending (Awaiting Parts/Approval)</option>
              </select>
            </div>

            <div>
              <label className="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                Work Resolution Notes / Log
              </label>
              <textarea
                rows={3}
                value={progressNote}
                onChange={(e) => setProgressNote(e.target.value)}
                placeholder="e.g. Replaced faulty power connector, verified printer self-test..."
                required
                className="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
              ></textarea>
            </div>

            <div className="pt-2 flex justify-end space-x-3">
              <button
                type="button"
                onClick={() => setSelectedTaskForUpdate(null)}
                className="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition"
              >
                Cancel
              </button>
              <button
                type="submit"
                className="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl shadow-md shadow-blue-600/30 transition"
              >
                Save Progress
              </button>
            </div>
          </form>
        </Modal>
      )}
    </div>
  );
};
