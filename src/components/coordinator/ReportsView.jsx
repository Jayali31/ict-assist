import React from 'react';
import { BarChart3, TrendingUp, Award, CheckCircle2, AlertCircle, Clock, Activity } from 'lucide-react';
import { useApp } from '../../context/AppContext';

export const ReportsView = () => {
  const { tickets, technicians } = useApp();

  const total = tickets.length;
  const resolved = tickets.filter(t => t.status === 'Resolved').length;
  const pending = tickets.filter(t => t.status === 'Pending').length;
  const inProgress = tickets.filter(t => t.status === 'In Progress').length;

  // Categories count
  const hardwareCount = tickets.filter(t => t.category === 'Hardware').length;
  const networkCount = tickets.filter(t => t.category === 'Network').length;
  const softwareCount = tickets.filter(t => t.category === 'Software').length;
  const telephoneCount = tickets.filter(t => t.category === 'Telephone').length;

  const maxCategoryCount = Math.max(hardwareCount, networkCount, softwareCount, telephoneCount, 1);

  return (
    <div className="p-8 max-w-6xl mx-auto space-y-8 animate-fadeIn">
      <div>
        <span className="text-xs font-bold uppercase tracking-wider text-blue-600">
          Analytics & Quality Control
        </span>
        <h1 className="text-2xl font-extrabold text-slate-900 mt-1">Helpdesk Reports & Performance</h1>
        <p className="text-xs text-slate-500 mt-0.5">
          Detailed metrics on department incident categories, response times, and technician efficiency.
        </p>
      </div>

      {/* Top 4 Metrics (Mockup Screen 9) */}
      <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div className="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs">
          <span className="text-xs font-semibold text-slate-500 uppercase">Total Requests</span>
          <h3 className="text-3xl font-extrabold text-slate-900 mt-2">{total + 45}</h3>
          <p className="text-[11px] text-blue-600 mt-1 font-medium">+12% from last month</p>
        </div>

        <div className="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs">
          <span className="text-xs font-semibold text-slate-500 uppercase">Resolved</span>
          <h3 className="text-3xl font-extrabold text-emerald-600 mt-2">{resolved + 38}</h3>
          <p className="text-[11px] text-emerald-600 mt-1 font-medium">92% SLA Compliance</p>
        </div>

        <div className="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs">
          <span className="text-xs font-semibold text-slate-500 uppercase">Pending</span>
          <h3 className="text-3xl font-extrabold text-amber-500 mt-2">{pending + 5}</h3>
          <p className="text-[11px] text-amber-600 mt-1 font-medium">Under 2 hr dispatch</p>
        </div>

        <div className="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs">
          <span className="text-xs font-semibold text-slate-500 uppercase">In Progress</span>
          <h3 className="text-3xl font-extrabold text-purple-600 mt-2">{inProgress + 3}</h3>
          <p className="text-[11px] text-purple-600 mt-1 font-medium">Active field visits</p>
        </div>
      </div>

      {/* Grid: Category Breakdown + Technician Leaderboard */}
      <div className="grid grid-cols-1 lg:grid-cols-12 gap-8">
        {/* Requests by Category */}
        <div className="lg:col-span-6 bg-white rounded-3xl border border-slate-200 p-6 shadow-xs">
          <div className="flex items-center justify-between pb-4 border-b border-slate-100 mb-6">
            <div>
              <h3 className="text-base font-bold text-slate-900">Requests by Category</h3>
              <p className="text-xs text-slate-500">Distribution of technical trouble tickets</p>
            </div>
            <BarChart3 size={20} className="text-slate-400" />
          </div>

          <div className="space-y-5">
            <div>
              <div className="flex justify-between text-xs font-semibold text-slate-700 mb-1.5">
                <span className="flex items-center gap-1.5">
                  <span className="w-2.5 h-2.5 rounded-full bg-blue-600"></span> Hardware
                </span>
                <span>{hardwareCount + 15} Requests</span>
              </div>
              <div className="w-full h-3 bg-slate-100 rounded-full overflow-hidden">
                <div 
                  className="h-full bg-blue-600 rounded-full transition-all duration-500" 
                  style={{ width: `${Math.min(100, ((hardwareCount + 15) / 35) * 100)}%` }}
                ></div>
              </div>
            </div>

            <div>
              <div className="flex justify-between text-xs font-semibold text-slate-700 mb-1.5">
                <span className="flex items-center gap-1.5">
                  <span className="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Network
                </span>
                <span>{networkCount + 12} Requests</span>
              </div>
              <div className="w-full h-3 bg-slate-100 rounded-full overflow-hidden">
                <div 
                  className="h-full bg-emerald-500 rounded-full transition-all duration-500" 
                  style={{ width: `${Math.min(100, ((networkCount + 12) / 35) * 100)}%` }}
                ></div>
              </div>
            </div>

            <div>
              <div className="flex justify-between text-xs font-semibold text-slate-700 mb-1.5">
                <span className="flex items-center gap-1.5">
                  <span className="w-2.5 h-2.5 rounded-full bg-amber-500"></span> Software
                </span>
                <span>{softwareCount + 10} Requests</span>
              </div>
              <div className="w-full h-3 bg-slate-100 rounded-full overflow-hidden">
                <div 
                  className="h-full bg-amber-500 rounded-full transition-all duration-500" 
                  style={{ width: `${Math.min(100, ((softwareCount + 10) / 35) * 100)}%` }}
                ></div>
              </div>
            </div>

            <div>
              <div className="flex justify-between text-xs font-semibold text-slate-700 mb-1.5">
                <span className="flex items-center gap-1.5">
                  <span className="w-2.5 h-2.5 rounded-full bg-purple-500"></span> Telephone / VoIP
                </span>
                <span>{telephoneCount + 6} Requests</span>
              </div>
              <div className="w-full h-3 bg-slate-100 rounded-full overflow-hidden">
                <div 
                  className="h-full bg-purple-500 rounded-full transition-all duration-500" 
                  style={{ width: `${Math.min(100, ((telephoneCount + 6) / 35) * 100)}%` }}
                ></div>
              </div>
            </div>
          </div>
        </div>

        {/* Technician Performance Table */}
        <div className="lg:col-span-6 bg-white rounded-3xl border border-slate-200 p-6 shadow-xs">
          <div className="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
            <div>
              <h3 className="text-base font-bold text-slate-900">Technician Performance</h3>
              <p className="text-xs text-slate-500">Resolved incident totals & efficiency</p>
            </div>
            <Award size={20} className="text-amber-500" />
          </div>

          <table className="w-full text-left text-xs">
            <thead>
              <tr className="border-b border-slate-100 text-slate-400 uppercase tracking-wider">
                <th className="pb-3 font-semibold">Staff Member</th>
                <th className="pb-3 font-semibold">Role</th>
                <th className="pb-3 font-semibold text-right">Resolved</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {technicians.map((t) => (
                <tr key={t.id} className="hover:bg-slate-50 transition">
                  <td className="py-3.5 flex items-center space-x-2.5">
                    <div className="w-7 h-7 rounded-lg bg-blue-600 text-white font-bold text-xs flex items-center justify-center">
                      {t.avatar}
                    </div>
                    <span className="font-bold text-slate-800">{t.name}</span>
                  </td>
                  <td className="py-3.5 text-slate-500">{t.specialization}</td>
                  <td className="py-3.5 text-right font-mono font-bold text-emerald-600 text-sm">
                    {t.resolvedCount}
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
