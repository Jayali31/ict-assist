import React, { useState } from 'react';
import { ArrowLeft, CheckCircle2 } from 'lucide-react';
import { useApp } from '../../context/AppContext';

export const NotificationSettings = () => {
  const { setActiveTab } = useApp();
  const [emailNotif, setEmailNotif] = useState(true);
  const [smsNotif, setSmsNotif] = useState(false);
  const [ticketStatusChanges, setTicketStatusChanges] = useState(true);
  const [techAssigned, setTechAssigned] = useState(true);
  const [weeklySummary, setWeeklySummary] = useState(false);
  const [saved, setSaved] = useState(false);

  const handleSave = () => {
    setSaved(true);
    setTimeout(() => setSaved(false), 2000);
  };

  return (
    <div className="p-8 max-w-2xl mx-auto space-y-6 animate-fadeIn">
      <button
        onClick={() => setActiveTab('profile')}
        className="flex items-center space-x-2 text-xs font-semibold text-slate-500 hover:text-slate-800 transition"
      >
        <ArrowLeft size={16} />
        <span>Back to Profile</span>
      </button>

      <div className="bg-white rounded-3xl border border-slate-200 p-8 shadow-xs">
        <div className="pb-5 border-b border-slate-100 mb-6">
          <h2 className="text-xl font-bold text-slate-900">Notification Settings</h2>
          <p className="text-xs text-slate-500 mt-1">
            Choose how you would like to receive updates regarding your IT service requests.
          </p>
        </div>

        <div className="space-y-6">
          {/* Notification Channels */}
          <div>
            <h4 className="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">
              Delivery Channels
            </h4>
            <div className="space-y-4">
              <label className="flex items-center justify-between cursor-pointer">
                <div>
                  <p className="text-xs font-bold text-slate-800">Email Notifications</p>
                  <p className="text-[11px] text-slate-500">Receive instant updates to your registered work email</p>
                </div>
                <input
                  type="checkbox"
                  checked={emailNotif}
                  onChange={(e) => setEmailNotif(e.target.checked)}
                  className="w-5 h-5 rounded text-blue-600 focus:ring-blue-500 cursor-pointer"
                />
              </label>

              <label className="flex items-center justify-between cursor-pointer">
                <div>
                  <p className="text-xs font-bold text-slate-800">SMS Alerts</p>
                  <p className="text-[11px] text-slate-500">Critical urgent priority updates delivered via SMS</p>
                </div>
                <input
                  type="checkbox"
                  checked={smsNotif}
                  onChange={(e) => setSmsNotif(e.target.checked)}
                  className="w-5 h-5 rounded text-blue-600 focus:ring-blue-500 cursor-pointer"
                />
              </label>
            </div>
          </div>

          {/* Alert Events */}
          <div className="pt-4 border-t border-slate-100">
            <h4 className="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">
              What You're Notified About
            </h4>
            <div className="space-y-4">
              <label className="flex items-center justify-between cursor-pointer">
                <div>
                  <p className="text-xs font-bold text-slate-800">Ticket Status Changes</p>
                  <p className="text-[11px] text-slate-500">When tickets transition between In Progress and Resolved</p>
                </div>
                <input
                  type="checkbox"
                  checked={ticketStatusChanges}
                  onChange={(e) => setTicketStatusChanges(e.target.checked)}
                  className="w-5 h-5 rounded text-blue-600 focus:ring-blue-500 cursor-pointer"
                />
              </label>

              <label className="flex items-center justify-between cursor-pointer">
                <div>
                  <p className="text-xs font-bold text-slate-800">Technician Assigned</p>
                  <p className="text-[11px] text-slate-500">Notification when a field technician is allocated to your branch</p>
                </div>
                <input
                  type="checkbox"
                  checked={techAssigned}
                  onChange={(e) => setTechAssigned(e.target.checked)}
                  className="w-5 h-5 rounded text-blue-600 focus:ring-blue-500 cursor-pointer"
                />
              </label>

              <label className="flex items-center justify-between cursor-pointer">
                <div>
                  <p className="text-xs font-bold text-slate-800">Weekly Summary Digest</p>
                  <p className="text-[11px] text-slate-500">Weekly overview of tickets submitted in your department</p>
                </div>
                <input
                  type="checkbox"
                  checked={weeklySummary}
                  onChange={(e) => setWeeklySummary(e.target.checked)}
                  className="w-5 h-5 rounded text-blue-600 focus:ring-blue-500 cursor-pointer"
                />
              </label>
            </div>
          </div>

          <div className="pt-4 border-t border-slate-100 flex items-center justify-between">
            {saved && (
              <span className="text-xs text-emerald-600 font-semibold flex items-center gap-1">
                <CheckCircle2 size={16} /> Preferences saved successfully!
              </span>
            )}
            <button
              type="button"
              onClick={handleSave}
              className="ml-auto px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold shadow-md shadow-blue-600/20 transition"
            >
              Save Preferences
            </button>
          </div>
        </div>
      </div>
    </div>
  );
};
