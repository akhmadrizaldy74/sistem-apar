import React, { useMemo, useState } from "react";

const DUMMY_ORDERS = [
  {
    id: "ORD-240416-001",
    date: "16 Apr 2026",
    customerName: "TEST DEAL PAY",
    phone: "081333333333",
    type: "Produk",
    itemCount: 1,
    items: ["APAR Dry Chemical Powder"],
    totalUnit: 1,
    priceType: "deal",
    finalPrice: 120000,
    originalPrice: 150000,
    status: "approved",
    hasNegoData: true,
    needPrimaryAction: false,
  },
  {
    id: "ORD-240416-002",
    date: "16 Apr 2026",
    customerName: "TEST NORMAL PAY",
    phone: "081222222222",
    type: "Produk",
    itemCount: 1,
    items: ["APAR Dry Chemical Powder"],
    totalUnit: 1,
    priceType: "normal",
    finalPrice: 150000,
    originalPrice: null,
    status: "approved",
    hasNegoData: false,
    needPrimaryAction: false,
  },
  {
    id: "ORD-240416-003",
    date: "16 Apr 2026",
    customerName: "TEST NEGO AUTO",
    phone: "081111111111",
    type: "Produk",
    itemCount: 1,
    items: ["APAR Dry Chemical Powder"],
    totalUnit: 1,
    priceType: "need_code",
    finalPrice: 123456,
    originalPrice: 150000,
    status: "need_code",
    hasNegoData: true,
    needPrimaryAction: true,
  },
  {
    id: "ORD-240414-001",
    date: "14 Apr 2026",
    customerName: "Kipli",
    phone: "087830665026",
    type: "Produk",
    itemCount: 3,
    items: ["APAR Carbon Dioxide (CO2)", "APAR Dry Chemical Powder"],
    totalUnit: 3,
    priceType: "pending",
    finalPrice: 1100000,
    originalPrice: null,
    status: "pending",
    hasNegoData: true,
    needPrimaryAction: true,
  },
  {
    id: "ORD-240414-002",
    date: "14 Apr 2026",
    customerName: "Fandi",
    phone: "087830665020",
    type: "Produk",
    itemCount: 2,
    items: ["APAR Dry Chemical Powder", "APAR Carbon Dioxide (CO2)"],
    totalUnit: 2,
    priceType: "rejected",
    finalPrice: 400000,
    originalPrice: null,
    status: "rejected",
    hasNegoData: true,
    needPrimaryAction: true,
  },
  {
    id: "ORD-240413-001",
    date: "13 Apr 2026",
    customerName: "Budi",
    phone: "087830665029",
    type: "Produk",
    itemCount: 2,
    items: ["APAR Dry Chemical Powder", "APAR Carbon Dioxide (CO2)"],
    totalUnit: 13,
    priceType: "rejected",
    finalPrice: 500000,
    originalPrice: null,
    status: "rejected",
    hasNegoData: true,
    needPrimaryAction: true,
  },
];

const FILTER_TABS = [
  { key: "all", label: "Semua" },
  { key: "queue", label: "Antrian Negosiasi" },
  { key: "nego", label: "Butuh Kode / Sudah Kode" },
  { key: "rejected", label: "Ditolak" },
];

function formatIDR(value) {
  return new Intl.NumberFormat("id-ID").format(value);
}

function SearchIcon(props) {
  return (
    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true" {...props}>
      <path
        d="M21 21l-4.35-4.35m1.35-5.15a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z"
        stroke="currentColor"
        strokeWidth="1.8"
        strokeLinecap="round"
        strokeLinejoin="round"
      />
    </svg>
  );
}

function MiniIcon({ children }) {
  return (
    <button
      type="button"
      className="h-10 w-10 rounded-xl border border-slate-200 bg-white text-slate-400 transition hover:border-slate-300 hover:text-slate-700"
      aria-label="Aksi sekunder"
    >
      {children}
    </button>
  );
}

function StatusBadge({ status }) {
  const map = {
    approved: { label: "Disetujui / Selesai", cls: "bg-emerald-50 text-emerald-700 border-emerald-200" },
    pending: { label: "Menunggu ACC", cls: "bg-amber-50 text-amber-700 border-amber-200" },
    need_code: { label: "Butuh Kode Nego", cls: "bg-blue-50 text-blue-700 border-blue-200" },
    rejected: { label: "Ditolak", cls: "bg-red-50 text-red-700 border-red-200" },
  };
  const current = map[status] ?? map.pending;

  return (
    <span
      className={`inline-flex items-center rounded-full border px-3 py-1 text-[11px] font-bold uppercase tracking-wide ${current.cls}`}
    >
      {current.label}
    </span>
  );
}

function PriceDisplay({ order }) {
  if (order.priceType === "deal") {
    return (
      <div className="space-y-0.5">
        <p className="text-lg font-extrabold text-emerald-700">Rp {formatIDR(order.finalPrice)}</p>
        <p className="text-xs font-semibold text-slate-400 line-through">Rp {formatIDR(order.originalPrice)}</p>
        <p className="text-[11px] font-bold text-emerald-600">Harga Deal</p>
      </div>
    );
  }

  if (order.priceType === "pending") {
    return (
      <div className="space-y-0.5">
        <p className="text-lg font-extrabold text-amber-700">Rp {formatIDR(order.finalPrice)}</p>
        <p className="text-[11px] font-bold text-amber-600">Menunggu ACC</p>
      </div>
    );
  }

  if (order.priceType === "need_code") {
    return (
      <div className="space-y-0.5">
        <p className="text-lg font-extrabold text-blue-700">Rp {formatIDR(order.finalPrice)}</p>
        {order.originalPrice ? (
          <p className="text-xs font-semibold text-slate-400 line-through">Rp {formatIDR(order.originalPrice)}</p>
        ) : null}
        <p className="text-[11px] font-bold text-blue-600">Butuh Kode</p>
      </div>
    );
  }

  if (order.priceType === "rejected") {
    return <p className="text-lg font-extrabold text-red-700">Rp {formatIDR(order.finalPrice)}</p>;
  }

  return <p className="text-lg font-extrabold text-slate-900">Rp {formatIDR(order.finalPrice)}</p>;
}

function ActionButtons({ order }) {
  const primaryLabel = order.needPrimaryAction ? "Kelola Nego" : "Detail";
  const primaryStyle = order.needPrimaryAction
    ? "border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100"
    : "border-slate-300 bg-white text-slate-700 hover:bg-slate-50";

  return (
    <div className="flex items-center justify-end gap-2">
      <button
        type="button"
        className={`rounded-xl border px-4 py-2 text-xs font-bold uppercase tracking-wide transition ${primaryStyle}`}
      >
        {primaryLabel}
      </button>

      <MiniIcon>
        <svg viewBox="0 0 24 24" className="mx-auto h-5 w-5" fill="none">
          <path
            d="M8 12h8m-8-4h6m-6 8h4m5 4H7a2 2 0 01-2-2V6a2 2 0 012-2h6l4 4v10a2 2 0 01-2 2z"
            stroke="currentColor"
            strokeWidth="1.7"
            strokeLinecap="round"
            strokeLinejoin="round"
          />
        </svg>
      </MiniIcon>

      <MiniIcon>
        <svg viewBox="0 0 24 24" className="mx-auto h-5 w-5" fill="none">
          <path
            d="M9 4h6m-7 3h8m-9 4h10m-9 4h8m-7 4h6M7 7l-1 12a2 2 0 002 2h8a2 2 0 002-2L17 7"
            stroke="currentColor"
            strokeWidth="1.7"
            strokeLinecap="round"
            strokeLinejoin="round"
          />
        </svg>
      </MiniIcon>
    </div>
  );
}

function FilterTabs({ active, onChange }) {
  const classes = {
    all: "data-[active=true]:bg-slate-900 data-[active=true]:text-white data-[active=true]:border-slate-900",
    queue:
      "data-[active=true]:bg-amber-600 data-[active=true]:text-white data-[active=true]:border-amber-600 data-[active=false]:text-amber-700 data-[active=false]:border-amber-200",
    nego:
      "data-[active=true]:bg-blue-600 data-[active=true]:text-white data-[active=true]:border-blue-600 data-[active=false]:text-blue-700 data-[active=false]:border-blue-200",
    rejected:
      "data-[active=true]:bg-red-600 data-[active=true]:text-white data-[active=true]:border-red-600 data-[active=false]:text-red-700 data-[active=false]:border-red-200",
  };

  return (
    <div className="flex flex-wrap gap-2">
      {FILTER_TABS.map((tab) => (
        <button
          key={tab.key}
          type="button"
          onClick={() => onChange(tab.key)}
          data-active={active === tab.key}
          className={`rounded-xl border px-4 py-2 text-xs font-bold uppercase tracking-wide transition hover:opacity-90 ${classes[tab.key]}`}
        >
          {tab.label}
        </button>
      ))}
    </div>
  );
}

function Row({ order }) {
  return (
    <article className="rounded-2xl border border-slate-200 bg-white p-4 md:p-5">
      <div className="grid grid-cols-1 gap-4 md:grid-cols-12 md:items-center">
        <div className="md:col-span-1">
          <p className="text-sm font-bold text-slate-800">{order.date}</p>
          <p className="text-xs font-semibold text-slate-400">{order.id}</p>
        </div>

        <div className="md:col-span-2">
          <p className="text-base font-extrabold text-slate-900">{order.customerName}</p>
          <p className="text-xs font-semibold text-slate-400">{order.phone}</p>
        </div>

        <div className="md:col-span-1">
          <span className="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-[11px] font-bold uppercase tracking-wide text-emerald-700">
            {order.type}
          </span>
        </div>

        <div className="md:col-span-3">
          <p className="text-base font-extrabold text-slate-900">{order.itemCount} item</p>
          <p className="mt-1 text-sm font-medium text-slate-500">{order.items.join(", ")}</p>
        </div>

        <div className="md:col-span-1">
          <p className="text-sm font-bold text-slate-800">{order.totalUnit} unit</p>
        </div>

        <div className="md:col-span-2">
          <PriceDisplay order={order} />
        </div>

        <div className="md:col-span-1">
          <StatusBadge status={order.status} />
        </div>

        <div className="md:col-span-1">
          <ActionButtons order={order} />
        </div>
      </div>
    </article>
  );
}

export default function NegotiationOrderDashboard() {
  const [query, setQuery] = useState("");
  const [activeTab, setActiveTab] = useState("all");

  const filtered = useMemo(() => {
    const q = query.trim().toLowerCase();

    return DUMMY_ORDERS.filter((row) => {
      const matchesSearch =
        !q ||
        row.customerName.toLowerCase().includes(q) ||
        row.items.join(" ").toLowerCase().includes(q) ||
        row.phone.includes(q);

      if (!matchesSearch) return false;

      if (activeTab === "queue") return row.status === "pending" || row.status === "need_code";
      if (activeTab === "nego") return row.hasNegoData;
      if (activeTab === "rejected") return row.status === "rejected";
      return true;
    });
  }, [query, activeTab]);

  return (
    <section className="mx-auto w-full max-w-7xl rounded-3xl border border-slate-200 bg-slate-50/60 p-6 md:p-8">
      <div className="rounded-2xl border border-slate-200 bg-white p-4 md:p-5">
        <label className="relative block">
          <SearchIcon className="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" />
          <input
            value={query}
            onChange={(e) => setQuery(e.target.value)}
            className="w-full rounded-xl border border-slate-200 bg-white py-3 pl-12 pr-4 text-sm font-semibold text-slate-800 placeholder:text-slate-400 focus:border-slate-300 focus:outline-none"
            placeholder="Cari pelanggan atau produk..."
          />
        </label>

        <div className="mt-4">
          <FilterTabs active={activeTab} onChange={setActiveTab} />
          <p className="mt-3 text-xs font-semibold text-slate-500">
            Antrian Negosiasi = menunggu ACC. Butuh Kode / Sudah Kode = semua pesanan yang punya data nego.
          </p>
        </div>
      </div>

      <div className="mt-5 space-y-3">
        {filtered.length ? (
          filtered.map((order) => <Row key={order.id} order={order} />)
        ) : (
          <div className="rounded-2xl border border-slate-200 bg-white p-10 text-center text-sm font-semibold text-slate-500">
            Data tidak ditemukan.
          </div>
        )}
      </div>
    </section>
  );
}

