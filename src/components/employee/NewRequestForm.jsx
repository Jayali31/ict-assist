import React, { useState } from 'react';
import { 
  ArrowLeft, 
  UploadCloud, 
  CheckCircle2, 
  HardDrive, 
  Wifi, 
  MonitorCheck, 
  PhoneCall, 
  AlertCircle 
} from 'lucide-react';
import { useApp } from '../../context/AppContext';

export const NewRequestForm = () => {
  const { currentUser, createTicket, setActiveTab } = useApp();

  const [branch, setBranch] = useState(currentUser?.branch || 'Colombo Main Office');
  const [department, setDepartment] = useState(currentUser?.department || 'Telecom & Admin');
  const [category, setCategory] = useState('Hardware');
  const [priority, setPriority] = useState('High');
  const [title, setTitle] = useState('');
  const [description, setDescription] = useState('');
  const [fileName, setFileName] = useState('');
  const [submitted, setSubmitted] = useState(false);

  const categories = [
    { id: 'Hardware', label: 'Hardware', icon: HardDrive },
    { id: 'Network', label: 'Network', icon: Wifi },
    { id: 'Software', label: 'Software', icon: MonitorCheck },
    { id: 'Telephone', label: 'Telephone', icon: PhoneCall },
  ];

  const priorities = [
    { id: 'High', label: 'High Priority', color: 'border-rose-500 bg-rose-50 text-rose-700' },
    { id: 'Medium', label: 'Medium Priority', color: 'border-amber-500 bg-amber-50 text-amber-700' },
    { id: 'Low', label: 'Low Priority', color: 'border-slate-300 bg-slate-50 text-slate-700' },
  ];

  const handleFileUpload = (e) => {
    if (e.target.files && e.target.files[0]) {
      setFileName(e.target.files[0].name);
    }
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    if (!title.trim() || !description.trim()) return;

    createTicket({
      title,
      category,
      priority,
      department,
      branch,
      description,
      attachment: fileName || null
    });

    setSubmitted(true);
    setTimeout(() => {
      setActiveTab('tickets');
    }, 1500);
  };

  return (
    <div className="p-8 max-w-4xl mx-auto animate-fadeIn">
      {/* Back button */}
      <button
        onClick={() => setActiveTab('dashboard')}
        className="flex items-center space-x-2 text-xs font-semibold text-slate-500 hover:text-slate-800 mb-6 transition"
      >
        <ArrowLeft size={16} />
        <span>Back to Dashboard</span>
      </button>

      <div className="bg-white rounded-3xl border border-slate-200 shadow-sm p-8">
        <div className="border-b border-slate-100 pb-5 mb-6">
          <h2 className="text-xl font-bold text-slate-900">New Service Request</h2>
          <p className="text-xs text-slate-500 mt-1">
            Fill in the details below to dispatch an ICT staff technician to your branch.
          </p>
        </div>

        {submitted ? (
          <div className="py-12 flex flex-col items-center justify-center text-center">
            <div className="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mb-4 animate-bounce">
              <CheckCircle2 size={36} />
            </div>
            <h3 className="text-lg font-bold text-slate-800">Request Submitted Successfully!</h3>
            <p className="text-xs text-slate-500 mt-1 max-w-sm">
              Your ticket has been logged and forwarded to the Helpdesk Coordinator. Redirecting to ticket status...
            </p>
          </div>
        ) : (
          <form onSubmit={handleSubmit} className="space-y-6">
            {/* Branch and Department */}
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label className="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                  Branch Name
                </label>
                <input
                  type="text"
                  value={branch}
                  onChange={(e) => setBranch(e.target.value)}
                  required
                  placeholder="e.g. Colombo Main Office"
                  className="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div>
                <label className="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                  Department
                </label>
                <input
                  type="text"
                  value={department}
                  onChange={(e) => setDepartment(e.target.value)}
                  required
                  placeholder="e.g. Telecom, Accounts, HR"
                  className="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
            </div>

            {/* Issue Category */}
            <div>
              <label className="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">
                Issue Category
              </label>
              <div className="grid grid-cols-2 sm:grid-cols-4 gap-3">
                {categories.map((cat) => {
                  const Icon = cat.icon;
                  const isSelected = category === cat.id;
                  return (
                    <button
                      key={cat.id}
                      type="button"
                      onClick={() => setCategory(cat.id)}
                      className={`flex items-center space-x-2.5 p-3 rounded-xl border text-xs font-semibold transition ${
                        isSelected
                          ? 'border-blue-600 bg-blue-50/80 text-blue-700 shadow-xs'
                          : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300'
                      }`}
                    >
                      <Icon size={16} className={isSelected ? 'text-blue-600' : 'text-slate-400'} />
                      <span>{cat.label}</span>
                    </button>
                  );
                })}
              </div>
            </div>

            {/* Priority */}
            <div>
              <label className="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">
                Priority Level
              </label>
              <div className="grid grid-cols-3 gap-3">
                {priorities.map((pri) => {
                  const isSelected = priority === pri.id;
                  return (
                    <button
                      key={pri.id}
                      type="button"
                      onClick={() => setPriority(pri.id)}
                      className={`py-2.5 px-3 rounded-xl border text-xs font-semibold text-center transition ${
                        isSelected
                          ? `${pri.color} shadow-xs font-bold ring-2 ring-offset-1 ring-blue-500/20`
                          : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'
                      }`}
                    >
                      {pri.label}
                    </button>
                  );
                })}
              </div>
            </div>

            {/* Title */}
            <div>
              <label className="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                Issue Summary / Title
              </label>
              <input
                type="text"
                value={title}
                onChange={(e) => setTitle(e.target.value)}
                required
                placeholder="e.g. Printer not working, paper jam and motor noise"
                className="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            {/* Description */}
            <div>
              <label className="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                Detailed Description
              </label>
              <textarea
                rows={4}
                value={description}
                onChange={(e) => setDescription(e.target.value)}
                required
                placeholder="Provide specific information (error codes, room number, affected computers)..."
                className="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
              ></textarea>
            </div>

            {/* Attach Photo (Optional) */}
            <div>
              <label className="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                Attach Photo or Screenshot (Optional)
              </label>
              <label className="border-2 border-dashed border-slate-200 hover:border-blue-400 rounded-2xl p-6 flex flex-col items-center justify-center cursor-pointer bg-slate-50/50 hover:bg-blue-50/20 transition">
                <UploadCloud size={28} className="text-slate-400 mb-2" />
                <span className="text-xs font-semibold text-slate-700">
                  {fileName ? `Selected: ${fileName}` : 'Click to browse or drop error image'}
                </span>
                <span className="text-[11px] text-slate-400 mt-0.5">PNG, JPG, or PDF up to 10MB</span>
                <input
                  type="file"
                  onChange={handleFileUpload}
                  className="hidden"
                  accept="image/*,.pdf"
                />
              </label>
            </div>

            {/* Submit button */}
            <div className="pt-2">
              <button
                type="submit"
                className="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-xl shadow-lg shadow-blue-600/25 transition transform active:scale-[0.99]"
              >
                Submit Request
              </button>
            </div>
          </form>
        )}
      </div>
    </div>
  );
};
