import React, { useState } from 'react';
import { Search, Filter, Clock, CheckCircle2, AlertTriangle, ArrowRight, UserCheck, Calendar } from 'lucide-react';
import { useApp } from '../../context/AppContext';
import { StatusBadge, PriorityBadge } from '../common/Badge';
import { Modal } from '../common/Modal';

export const TicketStatusView = () => {
  const { tickets, currentUser, currentUserRole } = useApp();
  const [filterStatus, setFilterStatus] = useState('All');
  const [searchQuery, setSearchQuery] = useState('');
  const [selectedTicket, setSelectedTicket] = useState(null);

  // If employee, show tickets created by them; if coordinator, show all tickets
  const displayTickets = tickets.filter(ticket => {
    if (currentUserRole === 'employee') {
      return ticket.createdBy === currentUser?.name || ticket.createdBy === 'A.M. Emandi';
    }
    return true; // Coordinator or other sees all
  });

  const filteredTickets = displayTickets.filter(ticket => {
    const matchesStatus = filterStatus === 'All' || ticket.status === filterStatus;
    const matchesSearch = 
      ticket.id.toLowerCase().includes(searchQuery.toLowerCase()) ||
      ticket.title.toLowerCase().includes(searchQuery.toLowerCase()) ||
      ticket.department.toLowerCase().includes(searchQuery.toLowerCase()) ||
      ticket.category.toLowerCase().includes(searchQuery.toLowerCase());
    return matchesStatus && matchesSearch;
  });

  return (
    <div className="p-8 max-w-6xl mx-auto space-y-6 animate-fadeIn">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h2 className="text-xl font-bold text-slate-900">
            {currentUserRole === 'coordinator' ? 'All System Tickets' : 'My Ticket History & Status'}
          </h2>
          <p className="text-xs text-slate-500 mt-0.5">
            Monitor real-time progress, technician assignments, and issue resolutions.
          </p>
        </div>

        {/* Filter Tabs */}
        <div className="flex items-center space-x-1 bg-slate-100 p-1 rounded-xl text-xs font-semibold">
          {['All', 'Pending', 'In Progress', 'Resolved'].map((tab) => (
            <button
              key={tab}
              onClick={() => setFilterStatus(tab)}
              className={`px-3 py-1.5 rounded-lg transition ${
                filterStatus === tab
                  ? 'bg-white text-blue-600 shadow-xs'
                  : 'text-slate-500 hover:text-slate-900'
              }`}
            >
              {tab}
            </button>
          ))}
        </div>
      </div>

      {/* Search Bar */}
      <div className="relative">
        <Search className="absolute left-3.5 top-3 text-slate-400" size={18} />
        <input
          type="text"
          value={searchQuery}
          onChange={(e) => setSearchQuery(e.target.value)}
          placeholder="Search by ticket ID, keyword, category, or branch..."
          className="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
        />
      </div>

      {/* Ticket Cards / Table */}
      <div className="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-sm">
        <div className="overflow-x-auto">
          <table className="w-full text-left text-xs">
            <thead>
              <tr className="bg-slate-50 border-b border-slate-100 text-slate-400 uppercase tracking-wider">
                <th className="py-3.5 px-6 font-semibold">Ticket ID</th>
                <th className="py-3.5 px-4 font-semibold">Issue Title</th>
                <th className="py-3.5 px-4 font-semibold">Category</th>
                <th className="py-3.5 px-4 font-semibold">Priority</th>
                <th className="py-3.5 px-4 font-semibold">Status</th>
                <th className="py-3.5 px-4 font-semibold">Assigned Tech</th>
                <th className="py-3.5 px-6 font-semibold text-right">Details</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {filteredTickets.length > 0 ? (
                filteredTickets.map((t) => (
                  <tr key={t.id} className="hover:bg-slate-50/70 transition">
                    <td className="py-4 px-6 font-mono font-bold text-blue-600">
                      #{t.id}
                    </td>
                    <td className="py-4 px-4 font-semibold text-slate-900 max-w-xs truncate">
                      {t.title}
                      <div className="text-[11px] text-slate-400 font-normal">
                        {t.branch} &bull; {t.department}
                      </div>
                    </td>
                    <td className="py-4 px-4 text-slate-600 font-medium">
                      {t.category}
                    </td>
                    <td className="py-4 px-4">
                      <PriorityBadge priority={t.priority} />
                    </td>
                    <td className="py-4 px-4">
                      <StatusBadge status={t.status} />
                    </td>
                    <td className="py-4 px-4 text-slate-600">
                      {t.assignedTo ? (
                        <span className="inline-flex items-center text-xs font-medium text-slate-700">
                          <span className="w-2 h-2 rounded-full bg-blue-500 mr-1.5"></span>
                          {t.assignedTo}
                        </span>
                      ) : (
                        <span className="text-slate-400 italic text-[11px]">Unassigned</span>
                      )}
                    </td>
                    <td className="py-4 px-6 text-right">
                      <button
                        onClick={() => setSelectedTicket(t)}
                        className="px-3 py-1.5 rounded-lg bg-blue-50 text-blue-600 font-semibold hover:bg-blue-600 hover:text-white transition"
                      >
                        View
                      </button>
                    </td>
                  </tr>
                ))
              ) : (
                <tr>
                  <td colSpan="7" className="py-12 text-center text-slate-400">
                    No tickets found matching your criteria.
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>

      {/* Ticket Details Modal */}
      {selectedTicket && (
        <Modal
          isOpen={!!selectedTicket}
          onClose={() => setSelectedTicket(null)}
          title={`Ticket Details: #${selectedTicket.id}`}
          maxWidth="max-w-xl"
        >
          <div className="space-y-4">
            <div className="flex items-center justify-between pb-3 border-b border-slate-100">
              <div className="flex items-center space-x-2">
                <StatusBadge status={selectedTicket.status} />
                <PriorityBadge priority={selectedTicket.priority} />
              </div>
              <span className="text-xs text-slate-400 flex items-center gap-1">
                <Calendar size={13} /> {selectedTicket.createdDate}
              </span>
            </div>

            <div>
              <h4 className="text-base font-bold text-slate-800">{selectedTicket.title}</h4>
              <p className="text-xs text-slate-500 mt-1">
                Branch: <strong>{selectedTicket.branch}</strong> &bull; Dept: <strong>{selectedTicket.department}</strong>
              </p>
            </div>

            <div className="bg-slate-50 p-4 rounded-xl text-xs text-slate-700 border border-slate-100">
              <p className="font-semibold text-slate-800 mb-1">Description:</p>
              <p className="leading-relaxed">{selectedTicket.description}</p>
            </div>

            {selectedTicket.attachment && (
              <div className="text-xs text-blue-600 bg-blue-50 p-2.5 rounded-lg border border-blue-100 flex items-center justify-between">
                <span>📎 Attachment: {selectedTicket.attachment}</span>
                <span className="text-[11px] font-semibold">View</span>
              </div>
            )}

            <div>
              <h5 className="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">
                Assigned Staff Technician
              </h5>
              <div className="p-3 bg-white border border-slate-200 rounded-xl flex items-center justify-between">
                <div className="flex items-center space-x-3">
                  <div className="w-8 h-8 rounded-full bg-blue-600 text-white font-bold text-xs flex items-center justify-center">
                    {selectedTicket.assignedTo ? selectedTicket.assignedTo.charAt(0) : '?'}
                  </div>
                  <div>
                    <p className="text-xs font-bold text-slate-800">
                      {selectedTicket.assignedTo || 'Pending Assignment'}
                    </p>
                    <p className="text-[11px] text-slate-400">
                      {selectedTicket.assignedTo ? 'Field Services' : 'Coordinator will assign shortly'}
                    </p>
                  </div>
                </div>
              </div>
            </div>

            {/* Timeline Notes */}
            <div>
              <h5 className="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">
                Progress Timeline
              </h5>
              <div className="space-y-2">
                {selectedTicket.progressNotes && selectedTicket.progressNotes.map((note, idx) => (
                  <div key={idx} className="flex items-start space-x-2 text-xs text-slate-600">
                    <span className="w-1.5 h-1.5 rounded-full bg-blue-500 mt-1.5 shrink-0"></span>
                    <span>{note}</span>
                  </div>
                ))}
              </div>
            </div>

            <div className="pt-4 flex justify-end">
              <button
                onClick={() => setSelectedTicket(null)}
                className="px-4 py-2 bg-slate-100 text-slate-700 hover:bg-slate-200 rounded-xl text-xs font-semibold transition"
              >
                Close
              </button>
            </div>
          </div>
        </Modal>
      )}
    </div>
  );
};
