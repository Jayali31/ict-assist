import React from 'react';
import { Bell, CheckCheck, Clock, CheckCircle2, AlertCircle, Info } from 'lucide-react';
import { useApp } from '../../context/AppContext';

export const NotificationsView = () => {
  const { notifications, markAllNotificationsRead } = useApp();

  const todayNotifications = notifications.filter(n => n.group === 'Today' || !n.group);
  const yesterdayNotifications = notifications.filter(n => n.group === 'Yesterday');

  const getIcon = (type) => {
    switch (type) {
      case 'success':
        return <CheckCircle2 size={16} className="text-emerald-500 shrink-0" />;
      case 'warning':
        return <AlertCircle size={16} className="text-rose-500 shrink-0" />;
      case 'info':
      default:
        return <Info size={16} className="text-blue-500 shrink-0" />;
    }
  };

  return (
    <div className="p-8 max-w-4xl mx-auto space-y-6 animate-fadeIn">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div>
          <h2 className="text-xl font-bold text-slate-900">Notifications & Alerts</h2>
          <p className="text-xs text-slate-500 mt-0.5">
            System dispatch updates, technician arrivals, and status changes.
          </p>
        </div>
        <button
          onClick={markAllNotificationsRead}
          className="flex items-center space-x-1.5 px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition shadow-xs"
        >
          <CheckCheck size={16} className="text-blue-600" />
          <span>Mark all as read</span>
        </button>
      </div>

      {/* Notifications List matching Screen 11 */}
      <div className="space-y-6">
        {/* TODAY */}
        <div>
          <h3 className="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3 px-1">
            Today
          </h3>
          <div className="space-y-2.5">
            {todayNotifications.map((n) => (
              <div
                key={n.id}
                className={`p-4 rounded-2xl border transition flex items-start space-x-3.5 ${
                  n.read
                    ? 'bg-white border-slate-200/80 text-slate-600'
                    : 'bg-white border-blue-200 shadow-xs ring-1 ring-blue-500/10'
                }`}
              >
                {getIcon(n.type)}
                <div className="flex-1">
                  <div className="flex items-center justify-between">
                    <p className="text-xs font-bold text-slate-800">{n.title}</p>
                    <span className="text-[11px] text-slate-400">{n.time}</span>
                  </div>
                  <p className="text-xs text-slate-500 mt-0.5">{n.subtitle}</p>
                </div>
              </div>
            ))}
          </div>
        </div>

        {/* YESTERDAY */}
        {yesterdayNotifications.length > 0 && (
          <div>
            <h3 className="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3 px-1">
              Yesterday
            </h3>
            <div className="space-y-2.5">
              {yesterdayNotifications.map((n) => (
                <div
                  key={n.id}
                  className="p-4 rounded-2xl border border-slate-200 bg-white/80 flex items-start space-x-3.5 text-slate-600"
                >
                  {getIcon(n.type)}
                  <div className="flex-1">
                    <div className="flex items-center justify-between">
                      <p className="text-xs font-bold text-slate-800">{n.title}</p>
                      <span className="text-[11px] text-slate-400">{n.time}</span>
                    </div>
                    <p className="text-xs text-slate-500 mt-0.5">{n.subtitle}</p>
                  </div>
                </div>
              ))}
            </div>
          </div>
        )}
      </div>
    </div>
  );
};
