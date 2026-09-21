import React from 'react';
import { useApp } from '../../context/AppContext';
import { StatusBadge, PriorityBadge } from '../common/Badge';

export const TaskQueueView = () => {
  const { tickets, currentUser } = useApp();

  const myTasks = tickets.filter(
    t => t.assignedTo === currentUser?.name || t.assignedTechId === currentUser?.id || t.assignedTo === 'K.P. Ratnasiri'
  );

  return (
    <div className="p-8 max-w-6xl mx-auto space-y-6 animate-fadeIn">
      <div>
        <h2 className="text-xl font-bold text-slate-900">Assigned Task Queue</h2>
        <p className="text-xs text-slate-500 mt-0.5">
          All technical issues currently assigned to your workbench.
        </p>
      </div>

      <div className="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-xs">
        <table className="w-full text-left text-xs">
          <thead>
            <tr className="bg-slate-50 border-b border-slate-100 text-slate-400 uppercase tracking-wider">
              <th className="py-3.5 px-6 font-semibold">Ticket ID</th>
              <th className="py-3.5 px-4 font-semibold">Task Title</th>
              <th className="py-3.5 px-4 font-semibold">Location</th>
              <th className="py-3.5 px-4 font-semibold">Priority</th>
              <th className="py-3.5 px-4 font-semibold">Status</th>
              <th className="py-3.5 px-6 font-semibold text-right">Date</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-slate-100">
            {myTasks.length > 0 ? (
              myTasks.map((t) => (
                <tr key={t.id} className="hover:bg-slate-50 transition">
                  <td className="py-4 px-6 font-mono font-bold text-blue-600">#{t.id}</td>
                  <td className="py-4 px-4 font-semibold text-slate-800">{t.title}</td>
                  <td className="py-4 px-4 text-slate-500">{t.branch} &bull; {t.department}</td>
                  <td className="py-4 px-4"><PriorityBadge priority={t.priority} /></td>
                  <td className="py-4 px-4"><StatusBadge status={t.status} /></td>
                  <td className="py-4 px-6 text-right text-slate-400">{t.createdDate}</td>
                </tr>
              ))
            ) : (
              <tr>
                <td colSpan="6" className="py-12 text-center text-slate-400">
                  No tasks currently assigned.
                </td>
              </tr>
            )}
          </tbody>
        </table>
      </div>
    </div>
  );
};
