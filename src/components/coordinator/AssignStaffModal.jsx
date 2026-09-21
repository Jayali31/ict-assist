import React, { useState } from 'react';
import { UserCheck, Check, ShieldCheck, Wrench } from 'lucide-react';
import { useApp } from '../../context/AppContext';
import { Modal } from '../common/Modal';
import { PriorityBadge } from '../common/Badge';

export const AssignStaffModal = ({ isOpen, onClose, ticket }) => {
  const { technicians, assignTicket } = useApp();
  const [selectedTechId, setSelectedTechId] = useState(technicians[0]?.id || '');

  if (!ticket) return null;

  const handleConfirm = () => {
    if (selectedTechId) {
      assignTicket(ticket.id, selectedTechId);
      onClose();
    }
  };

  return (
    <Modal
      isOpen={isOpen}
      onClose={onClose}
      title="Assign Staff / Technician"
      maxWidth="max-w-lg"
    >
      <div className="space-y-5">
        {/* Ticket Header Preview */}
        <div className="bg-slate-50 p-4 rounded-2xl border border-slate-200">
          <div className="flex items-center justify-between text-xs text-slate-500 mb-1">
            <span className="font-mono font-bold text-blue-600">#{ticket.id}</span>
            <div className="flex items-center space-x-1.5">
              <PriorityBadge priority={ticket.priority} />
              <span className="bg-blue-100 text-blue-700 px-2 py-0.5 rounded text-xs font-semibold">
                {ticket.category}
              </span>
            </div>
          </div>
          <h4 className="text-sm font-bold text-slate-900">{ticket.title}</h4>
          <p className="text-xs text-slate-500 mt-1">
            Branch: {ticket.branch} &bull; Dept: {ticket.department}
          </p>
        </div>

        {/* Technician Selection List */}
        <div>
          <label className="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">
            Select Available Technician
          </label>
          <div className="space-y-2">
            {technicians.map((tech) => {
              const isSelected = selectedTechId === tech.id;
              const isRecommended = tech.specialization.toLowerCase() === ticket.category.toLowerCase();
              return (
                <div
                  key={tech.id}
                  onClick={() => setSelectedTechId(tech.id)}
                  className={`flex items-center justify-between p-3.5 rounded-2xl border cursor-pointer transition ${
                    isSelected
                      ? 'border-blue-600 bg-blue-50/70 shadow-xs'
                      : 'border-slate-200 bg-white hover:border-slate-300'
                  }`}
                >
                  <div className="flex items-center space-x-3">
                    <div className={`w-9 h-9 rounded-xl flex items-center justify-center text-xs font-bold ${
                      isSelected ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-700'
                    }`}>
                      {tech.avatar}
                    </div>
                    <div>
                      <div className="flex items-center space-x-2">
                        <span className="text-xs font-bold text-slate-800">{tech.name}</span>
                        {isRecommended && (
                          <span className="text-[10px] bg-emerald-100 text-emerald-700 px-1.5 py-0.2 rounded font-semibold">
                            Recommended
                          </span>
                        )}
                      </div>
                      <p className="text-[11px] text-slate-500">
                        {tech.specialization} &bull; {tech.assignedCount} Active Tasks
                      </p>
                    </div>
                  </div>

                  <div className="flex items-center space-x-3">
                    <span className="text-xs px-2 py-0.5 rounded-full font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                      {tech.status}
                    </span>
                    <div className={`w-5 h-5 rounded-full border flex items-center justify-center ${
                      isSelected ? 'border-blue-600 bg-blue-600 text-white' : 'border-slate-300'
                    }`}>
                      {isSelected && <Check size={12} />}
                    </div>
                  </div>
                </div>
              );
            })}
          </div>
        </div>

        {/* Action buttons */}
        <div className="pt-2 flex items-center justify-end space-x-3">
          <button
            type="button"
            onClick={onClose}
            className="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition"
          >
            Cancel
          </button>
          <button
            type="button"
            onClick={handleConfirm}
            className="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-md shadow-blue-600/30 transition flex items-center space-x-1.5"
          >
            <UserCheck size={16} />
            <span>Confirm Assignment</span>
          </button>
        </div>
      </div>
    </Modal>
  );
};
