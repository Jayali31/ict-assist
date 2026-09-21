import React, { useState } from 'react';
import { ArrowLeft, CheckCircle2, Lock } from 'lucide-react';
import { useApp } from '../../context/AppContext';

export const ChangePasswordView = () => {
  const { setActiveTab } = useApp();
  const [currentPassword, setCurrentPassword] = useState('');
  const [newPassword, setNewPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [success, setSuccess] = useState(false);
  const [error, setError] = useState('');

  const handleSubmit = (e) => {
    e.preventDefault();
    if (newPassword !== confirmPassword) {
      setError('New passwords do not match!');
      return;
    }
    if (newPassword.length < 8) {
      setError('Password must be at least 8 characters.');
      return;
    }

    setError('');
    setSuccess(true);
    setCurrentPassword('');
    setNewPassword('');
    setConfirmPassword('');
    setTimeout(() => {
      setActiveTab('profile');
    }, 1500);
  };

  return (
    <div className="p-8 max-w-xl mx-auto space-y-6 animate-fadeIn">
      <button
        onClick={() => setActiveTab('profile')}
        className="flex items-center space-x-2 text-xs font-semibold text-slate-500 hover:text-slate-800 transition"
      >
        <ArrowLeft size={16} />
        <span>Back to Profile</span>
      </button>

      <div className="bg-white rounded-3xl border border-slate-200 p-8 shadow-xs">
        <div className="pb-5 border-b border-slate-100 mb-6 text-center">
          <div className="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-3">
            <Lock size={22} />
          </div>
          <h2 className="text-xl font-bold text-slate-900">Change Password</h2>
          <p className="text-xs text-slate-500 mt-1">
            Ensure your account is protected with a secure password.
          </p>
        </div>

        {success ? (
          <div className="py-8 text-center">
            <CheckCircle2 size={40} className="text-emerald-500 mx-auto mb-2 animate-bounce" />
            <h4 className="text-sm font-bold text-slate-800">Password Updated Successfully!</h4>
            <p className="text-xs text-slate-400 mt-1">Redirecting to profile...</p>
          </div>
        ) : (
          <form onSubmit={handleSubmit} className="space-y-4">
            {error && (
              <div className="p-3 bg-rose-50 text-rose-600 text-xs rounded-xl border border-rose-200">
                {error}
              </div>
            )}

            <div>
              <label className="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                Current Password
              </label>
              <input
                type="password"
                value={currentPassword}
                onChange={(e) => setCurrentPassword(e.target.value)}
                required
                placeholder="Enter current password"
                className="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <div>
              <label className="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                New Password
              </label>
              <input
                type="password"
                value={newPassword}
                onChange={(e) => setNewPassword(e.target.value)}
                required
                placeholder="Enter new password"
                className="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
              <p className="text-[11px] text-slate-400 mt-1">
                Must be at least 8 characters including a number and symbol.
              </p>
            </div>

            <div>
              <label className="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                Confirm New Password
              </label>
              <input
                type="password"
                value={confirmPassword}
                onChange={(e) => setConfirmPassword(e.target.value)}
                required
                placeholder="Re-enter new password"
                className="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <div className="pt-3">
              <button
                type="submit"
                className="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl text-xs shadow-md shadow-blue-600/30 transition"
              >
                Update Password
              </button>
            </div>
          </form>
        )}
      </div>
    </div>
  );
};
