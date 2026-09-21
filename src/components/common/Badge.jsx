import React from 'react';

export const StatusBadge = ({ status }) => {
  switch (status) {
    case 'In Progress':
      return (
        <span className="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
          <span className="w-1.5 h-1.5 rounded-full bg-amber-500 mr-1.5 animate-pulse"></span>
          In Progress
        </span>
      );
    case 'Resolved':
      return (
        <span className="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
          <span className="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
          Resolved
        </span>
      );
    case 'Pending':
    default:
      return (
        <span className="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
          <span className="w-1.5 h-1.5 rounded-full bg-blue-500 mr-1.5"></span>
          Pending
        </span>
      );
  }
};

export const PriorityBadge = ({ priority }) => {
  switch (priority) {
    case 'High':
      return (
        <span className="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-rose-50 text-rose-600 border border-rose-200">
          <span className="w-1.5 h-1.5 rounded-full bg-rose-500 mr-1"></span>
          High
        </span>
      );
    case 'Medium':
      return (
        <span className="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-amber-50 text-amber-600 border border-amber-200">
          <span className="w-1.5 h-1.5 rounded-full bg-amber-500 mr-1"></span>
          Medium
        </span>
      );
    case 'Low':
    default:
      return (
        <span className="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200">
          <span className="w-1.5 h-1.5 rounded-full bg-slate-400 mr-1"></span>
          Low
        </span>
      );
  }
};

export const AvailabilityBadge = ({ status }) => {
  const isAvailable = status === 'Available' || status === 'Free';
  return (
    <span
      className={`inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold ${
        isAvailable
          ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
          : 'bg-rose-50 text-rose-700 border border-rose-200'
      }`}
    >
      <span
        className={`w-1.5 h-1.5 rounded-full mr-1.5 ${
          isAvailable ? 'bg-emerald-500' : 'bg-rose-500'
        }`}
      ></span>
      {status}
    </span>
  );
};
