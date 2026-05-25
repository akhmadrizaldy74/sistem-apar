@extends('layouts.public')

@section('title', 'PD. Anugrah Utama - Penjualan & Layanan APAR Profesional')

@section('styles')
<style>
    :root {
        --brand-red: #dc2626;
        --brand-red-dark: #b91c1c;
        --brand-red-soft: #fee2e2;
        --brand-red-muted: #fef2f2;
        --brand-navy: #07111f;
        --brand-navy-soft: #10213a;
        --brand-ink: #0f172a;
        --brand-text: #1f2937;
        --brand-muted: #64748b;
        --brand-border: rgba(148, 163, 184, 0.18);
        --brand-surface: #f8fafc;
        --brand-surface-strong: #eef2f7;
        --brand-shadow: 0 24px 60px rgba(15, 23, 42, 0.10);
        --brand-shadow-soft: 0 18px 42px rgba(15, 23, 42, 0.08);
    }

    .container {
        max-width: 1180px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .section-pad {
        padding: 82px 0;
    }

    .section-soft {
        background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
    }

    .btn-primary,
    .btn-secondary,
    .btn-whatsapp,
    .btn-inline {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        border-radius: 16px;
        text-decoration: none;
        font-size: 14px;
        font-weight: 800;
        line-height: 1;
        transition: transform .25s ease, box-shadow .25s ease, background .25s ease, color .25s ease, border-color .25s ease;
    }

    .btn-primary,
    .btn-whatsapp,
    .btn-secondary {
        min-height: 50px;
        padding: 14px 24px;
    }

    .btn-primary {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        color: #fff;
        box-shadow: 0 18px 34px rgba(220, 38, 38, 0.25);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 24px 40px rgba(220, 38, 38, 0.32);
    }

    .btn-whatsapp {
        background: #16a34a;
        color: #fff;
        box-shadow: 0 18px 34px rgba(22, 163, 74, 0.20);
    }

    .btn-whatsapp:hover {
        transform: translateY(-2px);
        box-shadow: 0 22px 36px rgba(22, 163, 74, 0.28);
    }

    .btn-secondary {
        border: 1px solid rgba(220, 38, 38, 0.16);
        background: #fff;
        color: var(--brand-red-dark);
        box-shadow: 0 12px 24px rgba(255, 255, 255, 0.10);
    }

    .btn-secondary:hover {
        transform: translateY(-2px);
        border-color: rgba(220, 38, 38, 0.30);
        box-shadow: 0 18px 30px rgba(220, 38, 38, 0.12);
    }

    .btn-inline {
        padding: 12px 18px;
        border: 1px solid rgba(220, 38, 38, 0.16);
        background: #fff;
        color: var(--brand-red-dark);
        box-shadow: 0 14px 28px rgba(15, 23, 42, 0.07);
    }

    .btn-inline:hover {
        transform: translateY(-2px);
        border-color: rgba(220, 38, 38, 0.28);
    }

    .hero-section {
        position: relative;
        overflow: hidden;
        padding: 108px 0 86px;
        background:
            radial-gradient(circle at 12% 18%, rgba(248, 113, 113, 0.22), transparent 22%),
            radial-gradient(circle at 88% 16%, rgba(251, 191, 36, 0.12), transparent 20%),
            radial-gradient(circle at 76% 74%, rgba(239, 68, 68, 0.16), transparent 24%),
            linear-gradient(135deg, #07111f 0%, #10213a 46%, #152742 100%);
    }

    .hero-section::before {
        content: "";
        position: absolute;
        inset: 0;
        background-image:
            linear-gradient(rgba(255, 255, 255, 0.04) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255, 255, 255, 0.04) 1px, transparent 1px);
        background-size: 72px 72px;
        mask-image: linear-gradient(180deg, rgba(0, 0, 0, 0.55), transparent 90%);
        pointer-events: none;
    }

    .hero-section::after {
        content: "";
        position: absolute;
        inset: auto 0 0;
        height: 1px;
        background: linear-gradient(90deg, rgba(220, 38, 38, 0) 0%, rgba(220, 38, 38, 0.7) 30%, rgba(248, 113, 113, 0.5) 70%, rgba(220, 38, 38, 0) 100%);
    }

    .hero-inner {
        position: relative;
        z-index: 1;
        display: grid;
        grid-template-columns: minmax(0, 1.08fr) minmax(320px, 0.92fr);
        align-items: center;
        gap: 54px;
    }

    .hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 8px 16px;
        border-radius: 999px;
        border: 1px solid rgba(255, 255, 255, 0.16);
        background: rgba(255, 255, 255, 0.08);
        backdrop-filter: blur(10px);
        margin-bottom: 20px;
    }

    .hero-badge-dot {
        width: 9px;
        height: 9px;
        border-radius: 999px;
        background: #f87171;
        box-shadow: 0 0 0 6px rgba(248, 113, 113, 0.18);
    }

    .hero-badge span {
        color: #f8fafc;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.24em;
        text-transform: uppercase;
    }

    .hero-title {
        max-width: 700px;
        margin: 0 0 20px;
        color: #fff;
        font-size: 58px;
        font-weight: 900;
        line-height: 1.03;
        letter-spacing: -0.04em;
    }

    .hero-title span {
        color: #fca5a5;
    }

    .hero-sub {
        max-width: 610px;
        margin: 0 0 30px;
        color: rgba(226, 232, 240, 0.86);
        font-size: 17px;
        line-height: 1.78;
    }

    .hero-cta {
        display: flex;
        flex-wrap: wrap;
        gap: 14px;
        margin-bottom: 0;
    }

    .hero-visual {
        position: relative;
    }

    .hero-product-card {
        position: relative;
        padding: 20px;
        border-radius: 32px;
        background: rgba(255, 255, 255, 0.96);
        border: 1px solid rgba(255, 255, 255, 0.75);
        box-shadow: 0 30px 90px rgba(2, 6, 23, 0.35);
        overflow: hidden;
    }

    .hero-product-card::before {
        content: "";
        position: absolute;
        inset: -90px auto auto -40px;
        width: 180px;
        height: 180px;
        border-radius: 999px;
        background: rgba(220, 38, 38, 0.10);
        filter: blur(18px);
        pointer-events: none;
    }

    .hero-card-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 16px;
    }

    .hero-card-label,
    .hero-card-status {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.16em;
        text-transform: uppercase;
    }

    .hero-card-label {
        background: #fff1f2;
        color: var(--brand-red-dark);
    }

    .hero-card-status {
        background: rgba(15, 23, 42, 0.06);
        color: var(--brand-ink);
    }

    .hero-media {
        position: relative;
        min-height: 320px;
        border-radius: 26px;
        overflow: hidden;
        background:
            radial-gradient(circle at 50% 20%, rgba(248, 113, 113, 0.20), transparent 30%),
            linear-gradient(180deg, #fff5f5 0%, #ffffff 55%, #fff7ed 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 28px;
        border: 1px solid rgba(220, 38, 38, 0.08);
    }

    .hero-media img {
        max-width: 100%;
        max-height: 280px;
        width: auto;
        height: auto;
        object-fit: contain;
        display: block;
        filter: drop-shadow(0 26px 36px rgba(15, 23, 42, 0.14));
    }

    .hero-placeholder {
        width: 100%;
        min-height: 260px;
        border-radius: 24px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 12px;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.95), rgba(255, 241, 242, 0.95));
        color: var(--brand-red-dark);
        text-align: center;
        padding: 28px;
    }

    .hero-placeholder i {
        font-size: 44px;
    }

    .hero-placeholder strong {
        font-size: 16px;
        color: var(--brand-ink);
    }

    .hero-placeholder span {
        max-width: 220px;
        color: var(--brand-muted);
        font-size: 13px;
        line-height: 1.6;
    }

    .hero-floating-note {
        position: absolute;
        right: 20px;
        bottom: 20px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 14px;
        border-radius: 18px;
        background: rgba(7, 17, 31, 0.84);
        color: #fff;
        font-size: 12px;
        font-weight: 700;
        box-shadow: 0 18px 34px rgba(7, 17, 31, 0.20);
    }

    .hero-card-body {
        position: relative;
        z-index: 1;
        padding-top: 18px;
    }

    .hero-card-kicker {
        color: var(--brand-red-dark);
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        margin-bottom: 8px;
    }

    .hero-card-title {
        color: var(--brand-ink);
        font-size: 26px;
        font-weight: 900;
        line-height: 1.18;
        margin-bottom: 8px;
    }

    .hero-card-desc {
        color: var(--brand-muted);
        font-size: 14px;
        line-height: 1.7;
        margin-bottom: 18px;
    }

    .hero-spec-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 18px;
    }

    .hero-spec-item {
        border-radius: 18px;
        background: #f8fafc;
        border: 1px solid rgba(148, 163, 184, 0.12);
        padding: 14px 16px;
    }

    .hero-spec-label {
        color: #94a3b8;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        margin-bottom: 6px;
    }

    .hero-spec-value {
        color: var(--brand-ink);
        font-size: 14px;
        font-weight: 800;
        line-height: 1.4;
    }

    .section-head {
        text-align: center;
        margin-bottom: 46px;
    }

    .section-tag {
        display: inline-block;
        color: var(--brand-red-dark);
        font-size: 11px;
        font-weight: 900;
        letter-spacing: 0.24em;
        text-transform: uppercase;
        margin-bottom: 12px;
    }

    .section-title {
        margin: 0 0 14px;
        color: var(--brand-text);
        font-size: 40px;
        font-weight: 900;
        letter-spacing: -0.03em;
        line-height: 1.08;
    }

    .section-sub {
        max-width: 680px;
        margin: 0 auto;
        color: var(--brand-muted);
        font-size: 16px;
        line-height: 1.8;
    }

    .service-section {
        background:
            radial-gradient(circle at 0% 0%, rgba(220, 38, 38, 0.08), transparent 18%),
            linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
    }

    .service-grid,
    .steps-grid,
    .feat-grid,
    .katalog-grid,
    .testi-grid,
    .about-grid {
        display: grid;
        gap: 22px;
    }

    .service-grid {
        grid-template-columns: repeat(5, minmax(0, 1fr));
    }

    .service-card {
        position: relative;
        background: #fff;
        border: 1px solid rgba(148, 163, 184, 0.16);
        border-radius: 26px;
        padding: 28px 22px;
        box-shadow: var(--brand-shadow-soft);
        transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
        overflow: hidden;
    }

    .service-card::before {
        content: "";
        position: absolute;
        inset: 0 0 auto;
        height: 4px;
        background: linear-gradient(90deg, #dc2626 0%, #f97316 100%);
        opacity: .92;
    }

    .service-card:hover,
    .step-card:hover,
    .feat-card:hover,
    .katalog-card:hover,
    .testi-card:hover,
    .about-card:hover,
    .lokasi-card:hover {
        transform: translateY(-4px);
    }

    .service-card:hover {
        border-color: rgba(220, 38, 38, 0.18);
        box-shadow: 0 28px 48px rgba(15, 23, 42, 0.12);
    }

    .service-icon {
        width: 62px;
        height: 62px;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 18px;
        background: linear-gradient(135deg, #fff1f2 0%, #fee2e2 100%);
        color: var(--brand-red-dark);
        font-size: 22px;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.7);
    }

    .service-card-title,
    .step-title,
    .feat-title,
    .about-card-title {
        color: var(--brand-text);
        font-size: 18px;
        font-weight: 800;
        line-height: 1.35;
        margin-bottom: 8px;
    }

    .service-card-desc,
    .step-desc,
    .feat-desc,
    .katalog-spec,
    .about-card-desc {
        color: var(--brand-muted);
        font-size: 14px;
        line-height: 1.75;
    }

    .steps-grid {
        grid-template-columns: repeat(5, minmax(0, 1fr));
    }

    .step-card {
        background: #fff;
        border-radius: 26px;
        border: 1px solid rgba(148, 163, 184, 0.16);
        padding: 26px 22px;
        box-shadow: var(--brand-shadow-soft);
        transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
    }

    .step-card:hover {
        box-shadow: 0 28px 48px rgba(15, 23, 42, 0.10);
        border-color: rgba(220, 38, 38, 0.16);
    }

    .step-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 18px;
    }

    .step-num {
        width: 56px;
        height: 56px;
        flex-shrink: 0;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        color: #fff;
        font-size: 18px;
        font-weight: 900;
        box-shadow: 0 18px 30px rgba(220, 38, 38, 0.18);
    }

    .step-icon {
        width: 44px;
        height: 44px;
        flex-shrink: 0;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff1f2;
        color: var(--brand-red-dark);
        font-size: 18px;
    }

    .feat-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .feat-card {
        background: #fff;
        border: 1px solid rgba(148, 163, 184, 0.16);
        border-radius: 26px;
        padding: 28px 24px;
        box-shadow: var(--brand-shadow-soft);
        transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
    }

    .feat-card:hover {
        border-color: rgba(220, 38, 38, 0.18);
        box-shadow: 0 28px 48px rgba(15, 23, 42, 0.10);
    }

    .feat-icon {
        width: 56px;
        height: 56px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 18px;
        font-size: 22px;
    }

    .media-apar-section {
        position: relative;
        overflow: hidden;
        background:
            linear-gradient(180deg, #ffffff 0%, #f8fafc 52%, #ffffff 100%);
    }

    .media-apar-section::before {
        content: "";
        position: absolute;
        inset: 0;
        background-image:
            linear-gradient(rgba(220, 38, 38, 0.035) 1px, transparent 1px),
            linear-gradient(90deg, rgba(220, 38, 38, 0.035) 1px, transparent 1px);
        background-size: 70px 70px;
        mask-image: linear-gradient(180deg, transparent 0%, rgba(0, 0, 0, 0.5) 22%, rgba(0, 0, 0, 0.35) 74%, transparent 100%);
        pointer-events: none;
    }

    .media-apar-section .container {
        position: relative;
        z-index: 1;
    }

    .media-apar-shell {
        overflow: hidden;
        border-radius: 32px;
        border: 1px solid rgba(148, 163, 184, 0.16);
        background: rgba(255, 255, 255, 0.92);
        box-shadow: var(--brand-shadow-soft);
    }

    .media-apar-item {
        display: grid;
        grid-template-columns: minmax(260px, 0.82fr) minmax(0, 1.18fr);
        gap: 42px;
        align-items: center;
        padding: 40px 44px;
        border-bottom: 1px solid rgba(226, 232, 240, 0.92);
    }

    .media-apar-item:last-child {
        border-bottom: 0;
    }

    .media-apar-item--reverse {
        grid-template-columns: minmax(0, 1.18fr) minmax(260px, 0.82fr);
    }

    .media-apar-item--reverse .media-visual-wrap {
        order: 2;
    }

    .media-apar-item--reverse .media-copy {
        order: 1;
    }

    .media-visual-wrap {
        display: flex;
        justify-content: center;
    }

    .media-visual-stage {
        position: relative;
        width: min(100%, 300px);
        aspect-ratio: 1;
        border-radius: 30px;
        border: 1px solid rgba(220, 38, 38, 0.12);
        background:
            linear-gradient(145deg, #fff1f2 0%, #ffffff 64%, #f8fafc 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.86), 0 18px 34px rgba(15, 23, 42, 0.07);
    }

    .media-visual-stage::after {
        content: "";
        position: absolute;
        inset: 24px;
        border-radius: 26px;
        border: 1px dashed rgba(220, 38, 38, 0.20);
    }

    .media-main-icon {
        position: relative;
        z-index: 1;
        width: 138px;
        height: 138px;
        color: var(--brand-red-dark);
    }

    .media-copy {
        max-width: 680px;
    }

    .media-kicker {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 10px;
        color: var(--brand-red-dark);
        font-size: 11px;
        font-weight: 900;
        letter-spacing: 0.22em;
        text-transform: uppercase;
    }

    .media-kicker::before {
        content: "";
        width: 20px;
        height: 2px;
        border-radius: 999px;
        background: currentColor;
    }

    .media-title {
        margin: 0 0 12px;
        color: var(--brand-ink);
        font-size: 30px;
        font-weight: 900;
        line-height: 1.12;
    }

    .media-desc {
        margin: 0;
        color: var(--brand-muted);
        font-size: 15px;
        line-height: 1.9;
    }

    .media-class-list {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(146px, 170px));
        justify-content: flex-start;
        gap: 12px;
        margin-top: 22px;
    }

    .media-class-card {
        min-height: 132px;
        border-radius: 20px;
        border: 1px solid rgba(220, 38, 38, 0.22);
        background: linear-gradient(180deg, #ffffff 0%, #fff7f7 100%);
        color: var(--brand-red-dark);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-start;
        position: relative;
        overflow: hidden;
        padding: 14px 12px 13px;
        text-align: center;
        box-shadow: 0 14px 24px rgba(220, 38, 38, 0.08);
    }

    .media-class-card::before {
        content: "";
        position: absolute;
        inset: 8px;
        border-radius: 16px;
        border: 1px solid rgba(220, 38, 38, 0.08);
        pointer-events: none;
    }

    .media-class-visual {
        position: relative;
        z-index: 1;
        width: 46px;
        height: 46px;
        margin-bottom: 9px;
        border-radius: 16px;
        background: #fff;
        border: 1px solid rgba(220, 38, 38, 0.14);
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .media-class-visual svg {
        width: 32px;
        height: 32px;
        display: block;
    }

    .media-class-visual path,
    .media-class-visual circle {
        vector-effect: non-scaling-stroke;
    }

    .media-class-label {
        z-index: 2;
        color: var(--brand-ink);
        font-size: 12px;
        font-weight: 900;
        line-height: 1;
        margin-bottom: 6px;
    }

    .media-class-desc {
        position: relative;
        z-index: 2;
        color: var(--brand-muted);
        font-size: 11px;
        font-weight: 700;
        line-height: 1.45;
        margin: 0;
    }

    .product-head {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 20px;
        flex-wrap: wrap;
        margin-bottom: 42px;
    }

    .product-head-copy .section-tag,
    .product-head-copy .section-title,
    .product-head-copy .section-sub {
        text-align: left;
        margin-left: 0;
    }

    .product-head-copy .section-sub {
        max-width: 620px;
    }

    .product-head-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
    }

    .katalog-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    .katalog-card {
        height: 100%;
        background: #fff;
        border: 1px solid rgba(148, 163, 184, 0.16);
        border-radius: 28px;
        overflow: hidden;
        box-shadow: var(--brand-shadow-soft);
        transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
    }

    .katalog-card:hover {
        border-color: rgba(220, 38, 38, 0.20);
        box-shadow: 0 30px 50px rgba(15, 23, 42, 0.12);
    }

    .katalog-card-link {
        height: 100%;
        display: flex;
        flex-direction: column;
        text-decoration: none;
    }

    .katalog-img {
        position: relative;
        height: 240px;
        background:
            radial-gradient(circle at 50% 18%, rgba(248, 113, 113, 0.18), transparent 28%),
            linear-gradient(180deg, #fff7f7 0%, #ffffff 62%, #fff7ed 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 28px;
        overflow: hidden;
    }

    .katalog-img img {
        max-width: 100%;
        max-height: 188px;
        width: auto;
        height: auto;
        object-fit: contain;
        display: block;
        transition: transform .35s ease;
        filter: drop-shadow(0 20px 30px rgba(15, 23, 42, 0.12));
    }

    .katalog-card:hover .katalog-img img {
        transform: scale(1.04);
    }

    .katalog-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #d1d5db;
    }

    .katalog-placeholder svg {
        width: 48px;
        height: 48px;
    }

    .katalog-badge {
        position: absolute;
        top: 18px;
        left: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 7px 12px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.92);
        color: var(--brand-red-dark);
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        box-shadow: 0 14px 24px rgba(15, 23, 42, 0.08);
    }

    .katalog-body {
        display: flex;
        flex-direction: column;
        flex: 1;
        padding: 24px;
    }

    .katalog-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 14px;
    }

    .katalog-chip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 7px 10px;
        border-radius: 999px;
        background: #f8fafc;
        color: #475569;
        border: 1px solid rgba(148, 163, 184, 0.14);
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 0.1em;
        text-transform: uppercase;
    }

    .katalog-name {
        color: var(--brand-ink);
        font-size: 20px;
        font-weight: 900;
        line-height: 1.3;
        margin: 0 0 10px;
    }

    .katalog-spec {
        margin: 0 0 20px;
    }

    .katalog-footer {
        margin-top: auto;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding-top: 18px;
        border-top: 1px solid rgba(226, 232, 240, 0.9);
    }

    .katalog-price {
        color: var(--brand-red-dark);
        font-size: 22px;
        font-weight: 900;
        line-height: 1;
    }

    .katalog-order {
        color: var(--brand-ink);
        font-size: 13px;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .testi-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .testi-grid-sparse {
        max-width: 880px;
        margin: 0 auto;
        grid-template-columns: repeat(auto-fit, minmax(280px, 360px));
        justify-content: center;
    }

    .testi-card {
        position: relative;
        background: #fff;
        border: 1px solid rgba(148, 163, 184, 0.16);
        border-radius: 28px;
        padding: 30px 26px;
        box-shadow: var(--brand-shadow-soft);
        transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
    }

    .testi-card:hover {
        border-color: rgba(220, 38, 38, 0.18);
        box-shadow: 0 28px 48px rgba(15, 23, 42, 0.10);
    }

    .testi-quote {
        position: absolute;
        top: 22px;
        right: 22px;
        width: 42px;
        height: 42px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff5f5;
        color: var(--brand-red-dark);
        font-size: 16px;
    }

    .testi-stars {
        color: #f59e0b;
        font-size: 15px;
        margin-bottom: 18px;
    }

    .testi-text {
        color: #475569;
        font-size: 15px;
        line-height: 1.9;
        margin-bottom: 22px;
    }

    .testi-divider {
        height: 1px;
        background: rgba(226, 232, 240, 0.95);
        margin-bottom: 18px;
    }

    .testi-author {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .testi-avatar {
        width: 48px;
        height: 48px;
        flex-shrink: 0;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #fee2e2 0%, #fff1f2 100%);
        color: var(--brand-red-dark);
        font-size: 16px;
        font-weight: 900;
    }

    .testi-name {
        color: var(--brand-ink);
        font-size: 15px;
        font-weight: 900;
    }

    .testi-role {
        color: #94a3b8;
        font-size: 12px;
        font-weight: 700;
        margin-top: 3px;
    }

    .about-section {
        position: relative;
        overflow: hidden;
        background:
            radial-gradient(circle at 14% 18%, rgba(248, 113, 113, 0.12), transparent 22%),
            radial-gradient(circle at 86% 18%, rgba(248, 113, 113, 0.08), transparent 18%),
            linear-gradient(135deg, #07111f 0%, #10213a 55%, #162945 100%);
    }

    .about-section::before {
        content: "";
        position: absolute;
        inset: 0;
        background-image:
            linear-gradient(rgba(255, 255, 255, 0.035) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255, 255, 255, 0.035) 1px, transparent 1px);
        background-size: 82px 82px;
        mask-image: linear-gradient(180deg, rgba(0, 0, 0, 0.7), transparent 92%);
        pointer-events: none;
    }

    .about-inner {
        position: relative;
        z-index: 1;
        display: grid;
        grid-template-columns: minmax(0, 1.05fr) minmax(0, 0.95fr);
        gap: 42px;
        align-items: center;
    }

    .about-tag {
        color: #fca5a5;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: 0.22em;
        text-transform: uppercase;
        margin-bottom: 12px;
    }

    .about-title {
        color: #fff;
        font-size: 40px;
        font-weight: 900;
        line-height: 1.12;
        letter-spacing: -0.03em;
        margin: 0 0 16px;
    }

    .about-desc {
        color: rgba(226, 232, 240, 0.78);
        font-size: 16px;
        line-height: 1.85;
        margin-bottom: 24px;
    }

    .about-points {
        display: grid;
        gap: 14px;
        margin-bottom: 28px;
    }

    .about-point {
        display: flex;
        gap: 12px;
        align-items: flex-start;
        color: rgba(226, 232, 240, 0.82);
        font-size: 14px;
        line-height: 1.7;
    }

    .about-point i {
        width: 28px;
        height: 28px;
        flex-shrink: 0;
        border-radius: 999px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(248, 113, 113, 0.14);
        color: #fca5a5;
        font-size: 12px;
        margin-top: 1px;
    }

    .about-cta {
        display: flex;
        flex-wrap: wrap;
        gap: 14px;
    }

    .about-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .about-card {
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.11);
        border-radius: 26px;
        padding: 24px;
        backdrop-filter: blur(14px);
        transition: transform .25s ease, border-color .25s ease, background .25s ease;
    }

    .about-card:hover {
        background: rgba(255, 255, 255, 0.12);
        border-color: rgba(248, 113, 113, 0.20);
    }

    .about-icon {
        width: 54px;
        height: 54px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 18px;
        font-size: 22px;
    }

    .about-card-title {
        color: #fff;
    }

    .about-card-desc {
        color: rgba(226, 232, 240, 0.72);
    }

    .lokasi-grid {
        display: grid;
        grid-template-columns: 340px minmax(0, 1fr);
        gap: 24px;
        align-items: stretch;
    }

    .lokasi-info {
        display: grid;
        gap: 16px;
    }

    .lokasi-card {
        background: #fff;
        border: 1px solid rgba(148, 163, 184, 0.16);
        border-radius: 24px;
        padding: 20px;
        display: flex;
        align-items: flex-start;
        gap: 14px;
        box-shadow: var(--brand-shadow-soft);
        transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
    }

    .lokasi-card:hover {
        border-color: rgba(220, 38, 38, 0.18);
        box-shadow: 0 24px 42px rgba(15, 23, 42, 0.10);
    }

    .lokasi-icon {
        width: 48px;
        height: 48px;
        flex-shrink: 0;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }

    .lokasi-label {
        color: #94a3b8;
        font-size: 10px;
        font-weight: 900;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        margin-bottom: 6px;
    }

    .lokasi-text {
        color: var(--brand-ink);
        font-size: 15px;
        font-weight: 800;
        line-height: 1.55;
        text-decoration: none;
    }

    .lokasi-sub {
        color: var(--brand-muted);
        font-size: 13px;
        line-height: 1.65;
        margin-top: 4px;
    }

    .lokasi-map {
        min-height: 390px;
        border-radius: 30px;
        overflow: hidden;
        border: 1px solid rgba(148, 163, 184, 0.16);
        box-shadow: var(--brand-shadow);
        background: #fff;
    }

    .landing-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 12px;
        min-height: 220px;
        border-radius: 28px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        border: 1px dashed rgba(148, 163, 184, 0.22);
        color: var(--brand-muted);
        text-align: center;
        padding: 28px;
    }

    .landing-empty i {
        color: var(--brand-red-dark);
        font-size: 36px;
    }

    @media (max-width: 1366px) {
        .hero-title {
            font-size: 52px;
        }

        .service-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .steps-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .katalog-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 1024px) {
        .section-pad {
            padding: 74px 0;
        }

        .hero-section {
            padding: 102px 0 76px;
        }

        .hero-inner,
        .about-inner,
        .lokasi-grid {
            grid-template-columns: 1fr;
        }

        .feat-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .hero-title,
        .section-title,
        .about-title {
            max-width: none;
        }

        .media-apar-item,
        .media-apar-item--reverse {
            grid-template-columns: 1fr;
            gap: 28px;
        }

        .media-apar-item--reverse .media-visual-wrap,
        .media-apar-item--reverse .media-copy {
            order: initial;
        }

        .hero-title {
            font-size: 46px;
        }

        .section-title,
        .about-title {
            font-size: 34px;
        }

        .testi-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .service-grid,
        .about-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 768px) {
        .container {
            padding: 0 16px;
        }

        .section-pad {
            padding: 62px 0;
        }

        .hero-section {
            padding: 94px 0 62px;
        }

        .hero-title {
            font-size: 38px;
        }

        .hero-sub,
        .section-sub,
        .about-desc {
            font-size: 15px;
        }

        .service-grid,
        .feat-grid,
        .katalog-grid,
        .testi-grid,
        .about-grid {
            grid-template-columns: 1fr;
        }

        .hero-spec-grid {
            grid-template-columns: 1fr;
        }

        .media-apar-shell {
            border-radius: 26px;
        }

        .media-apar-item {
            padding: 30px 24px;
        }

        .media-visual-stage {
            width: min(100%, 260px);
            border-radius: 24px;
        }

        .media-title {
            font-size: 26px;
        }

        .section-head {
            margin-bottom: 34px;
        }

        .section-title,
        .about-title {
            font-size: 30px;
        }

        .product-head {
            margin-bottom: 32px;
        }

        .product-head-actions,
        .hero-cta,
        .about-cta {
            flex-direction: column;
        }

        .product-head-actions > *,
        .hero-cta > *,
        .about-cta > * {
            width: 100%;
        }

        .hero-product-card,
        .service-card,
        .step-card,
        .feat-card,
        .media-apar-shell,
        .katalog-card,
        .testi-card,
        .about-card,
        .lokasi-card {
            border-radius: 24px;
        }

        .katalog-img {
            height: 220px;
        }

        .lokasi-map {
            min-height: 320px;
        }

        .hero-floating-note {
            position: static;
            margin-top: 14px;
            justify-content: center;
        }
    }

    @media (max-width: 640px) {
        .hero-title {
            font-size: 34px;
        }

        .section-title,
        .about-title {
            font-size: 28px;
        }

        .hero-product-card {
            padding: 16px;
        }

        .hero-media {
            min-height: 260px;
            padding: 20px;
        }

        .hero-media img {
            max-height: 220px;
        }

        .hero-point {
            padding: 14px;
        }

        .media-apar-item {
            padding: 26px 18px;
        }

        .media-main-icon {
            width: 116px;
            height: 116px;
        }

        .media-class-list {
            grid-template-columns: 1fr;
            gap: 10px;
        }

        .media-class-card {
            min-height: auto;
            align-items: flex-start;
            padding: 14px 16px;
            border-radius: 18px;
            text-align: left;
        }

        .media-class-visual {
            margin-bottom: 8px;
        }

        .katalog-body,
        .testi-card,
        .service-card,
        .step-card,
        .feat-card,
        .about-card {
            padding: 22px 20px;
        }

        .lokasi-card {
            padding: 18px;
        }

        .btn-primary,
        .btn-secondary,
        .btn-whatsapp,
        .btn-inline {
            font-size: 13px;
        }
    }
</style>
@endsection

@section('content')
@php
    $orderEntryUrl = auth()->check() ? route('order.create') : route('login');
    $waContact = env('WHATSAPP_CONTACT', '6285128008030');
    $heroProduct = $produks->first();
    $testimoniLayout = $testimonis->count() > 0 && $testimonis->count() <= 2 ? 'testi-grid testi-grid-sparse' : 'testi-grid';
@endphp

<section class="hero-section">
    <div class="container">
        <div class="hero-inner">
            <div data-reveal>
                <div class="hero-badge">
                    <span class="hero-badge-dot"></span>
                    <span>Layanan APAR Profesional</span>
                </div>
                <h1 class="hero-title">
                    Solusi <span>Penjualan dan Layanan APAR</span> yang Lebih Meyakinkan untuk Operasional Anda
                </h1>
                <p class="hero-sub">
                    PD. Anugrah Utama melayani pembelian APAR, refill, service, inspeksi, dan konsultasi kebutuhan proteksi kebakaran dengan alur yang rapi, harga jelas, dan respons yang cepat.
                </p>
                <div class="hero-cta">
                    <a href="{{ $orderEntryUrl }}" class="btn-primary">
                        <i class="fa-solid fa-cart-shopping"></i>
                        Pesan Sekarang
                    </a>
                    <a href="https://wa.me/{{ $waContact }}?text={{ urlencode('Halo, saya ingin konsultasi dan pemesanan APAR.') }}" target="_blank" rel="noopener noreferrer" class="btn-whatsapp">
                        <i class="fa-brands fa-whatsapp"></i>
                        Hubungi WhatsApp
                    </a>
                </div>
            </div>

            <div class="hero-visual" data-reveal>
                <div class="hero-product-card">
                    <div class="hero-card-top">
                        <span class="hero-card-label">Produk Unggulan</span>
                        <span class="hero-card-status">
                            <i class="fa-solid fa-shield-halved"></i>
                            Siap Dipesan
                        </span>
                    </div>

                    <div class="hero-media">
                        @if($heroProduct && $heroProduct->gambar)
                            <img src="{{ asset('storage/' . $heroProduct->gambar) }}" alt="{{ $heroProduct->nama }}">
                        @else
                            <div class="hero-placeholder">
                                <i class="fa-solid fa-fire-extinguisher"></i>
                                <strong>Produk APAR Siap Ditampilkan</strong>
                                <span>Tambahkan gambar produk APAR untuk menampilkan visual yang lebih kuat pada halaman utama.</span>
                            </div>
                        @endif
                        <div class="hero-floating-note">
                            <i class="fa-solid fa-store"></i>
                            Tersedia pembelian langsung dan pemesanan online
                        </div>
                    </div>

                    <div class="hero-card-body">
                        <p class="hero-card-kicker">{{ $heroProduct?->jenisApar?->nama ?? 'Produk APAR Profesional' }}</p>
                        <h2 class="hero-card-title">{{ $heroProduct?->nama ?? 'APAR Berkualitas untuk Kebutuhan Operasional Anda' }}</h2>
                        <p class="hero-card-desc">
                            {{ $heroProduct ? 'Produk APAR dipilih dari data sistem agar pelanggan langsung melihat spesifikasi utama, harga, dan tampilan yang lebih meyakinkan.' : 'Landing page ini disiapkan untuk menampilkan produk APAR unggulan lengkap dengan kapasitas, merek, dan harga yang jelas.' }}
                        </p>
                        <div class="hero-spec-grid">
                            <div class="hero-spec-item">
                                <p class="hero-spec-label">Jenis APAR</p>
                                <p class="hero-spec-value">{{ $heroProduct?->jenisApar?->nama ?? 'APAR' }}</p>
                            </div>
                            <div class="hero-spec-item">
                                <p class="hero-spec-label">Ukuran</p>
                                <p class="hero-spec-value">{{ $heroProduct?->kapasitas ?: 'Ikuti data produk' }}</p>
                            </div>
                            <div class="hero-spec-item">
                                <p class="hero-spec-label">Merek</p>
                                <p class="hero-spec-value">{{ $heroProduct?->merek ?: 'Sesuai stok tersedia' }}</p>
                            </div>
                            <div class="hero-spec-item">
                                <p class="hero-spec-label">Harga</p>
                                <p class="hero-spec-value">{{ $heroProduct ? 'Rp ' . number_format($heroProduct->harga, 0, ',', '.') : 'Hubungi admin' }}</p>
                            </div>
                        </div>
                        <a href="{{ $heroProduct ? route('produk.show', $heroProduct) : route('produk.index') }}" class="btn-inline">
                            Lihat Detail Produk
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="service-section section-pad">
    <div class="container">
        <div class="section-head" data-reveal>
            <p class="section-tag">Layanan APAR</p>
            <h2 class="section-title">Layanan yang Tersusun Lebih Jelas untuk Kebutuhan APAR</h2>
            <p class="section-sub">Setiap layanan disusun agar mudah dipahami pelanggan, mulai dari pembelian produk hingga perawatan APAR secara berkala.</p>
        </div>

        @php
            $services = [
                ['title' => 'Penjualan APAR', 'desc' => 'Pilihan produk APAR untuk rumah, toko, kantor, gudang, proyek, dan area operasional lainnya.', 'icon' => 'fa-cart-shopping'],
                ['title' => 'Refill APAR', 'desc' => 'Isi ulang APAR dengan prosedur yang rapi dan harga yang mengikuti data layanan yang sudah tersedia.', 'icon' => 'fa-arrows-rotate'],
                ['title' => 'Service APAR', 'desc' => 'Perawatan dan pemeriksaan komponen APAR agar kondisi unit tetap siap digunakan.', 'icon' => 'fa-screwdriver-wrench'],
                ['title' => 'Inspeksi & Testing', 'desc' => 'Pengecekan tekanan, kondisi tabung, segel, dan komponen penting APAR lainnya.', 'icon' => 'fa-clipboard-check'],
                ['title' => 'Konsultasi APAR', 'desc' => 'Diskusikan kebutuhan APAR berdasarkan lokasi, risiko, dan kapasitas yang paling sesuai.', 'icon' => 'fa-comments'],
            ];
        @endphp

        <div class="service-grid">
            @foreach($services as $service)
                <article class="service-card" data-reveal>
                    <div class="service-icon">
                        <i class="fa-solid {{ $service['icon'] }}"></i>
                    </div>
                    <h3 class="service-card-title">{{ $service['title'] }}</h3>
                    <p class="service-card-desc">{{ $service['desc'] }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>

<section class="media-apar-section section-pad">
    <div class="container">
        <div class="section-head" data-reveal>
            <p class="section-tag">Jenis Media APAR</p>
            <h2 class="section-title">Kenali Media Pemadam APAR yang Sesuai Kebutuhan</h2>
            <p class="section-sub">Informasi media APAR disusun ringkas agar pelanggan lebih mudah memahami fungsi dry chemical powder, foam, dan karbon dioksida sebelum memilih produk atau layanan.</p>
        </div>

        @php
            $mediaAparItems = [
                [
                    'key' => 'powder',
                    'title' => 'Dry Chemical Powder',
                    'desc' => 'APAR dry chemical powder adalah media pemadam api serbaguna yang dapat digunakan untuk kebakaran kelas A, B, dan C. Cocok digunakan di perkantoran, sekolah, tempat ibadah, rumah sakit, hotel, pusat perbelanjaan, rumah tinggal, gudang, kendaraan, dan area usaha umum lainnya.',
                    'classes' => ['A', 'B', 'C'],
                    'reverse' => false,
                ],
                [
                    'key' => 'foam',
                    'title' => 'Foam / Busa',
                    'desc' => 'APAR foam banyak digunakan untuk kebakaran yang melibatkan bahan padat dan cairan mudah terbakar. Media ini bekerja dengan cara mendinginkan serta menutup permukaan sumber api sehingga membantu mempercepat proses pemadaman.',
                    'classes' => ['A', 'B'],
                    'reverse' => true,
                ],
                [
                    'key' => 'co2',
                    'title' => 'Karbon Dioksida (CO2)',
                    'desc' => 'APAR karbon dioksida cocok digunakan untuk kebakaran akibat peralatan elektronik dan cairan yang mudah terbakar. Media CO2 tidak meninggalkan residu sehingga sesuai untuk area yang memiliki perangkat elektronik.',
                    'classes' => ['B', 'C'],
                    'reverse' => false,
                ],
            ];
        @endphp

        <div class="media-apar-shell" data-reveal>
            @foreach($mediaAparItems as $item)
                <article class="media-apar-item {{ $item['reverse'] ? 'media-apar-item--reverse' : '' }}">
                    <div class="media-visual-wrap">
                        <div class="media-visual-stage" aria-hidden="true">
                            @if($item['key'] === 'powder')
                                <svg class="media-main-icon" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M48 34h24l6 17v42a9 9 0 0 1-9 9H51a9 9 0 0 1-9-9V51l6-17Z" stroke="currentColor" stroke-width="4" stroke-linejoin="round"/>
                                    <path d="M51 24h18v10H51V24Z" stroke="currentColor" stroke-width="4" stroke-linejoin="round"/>
                                    <path d="M68 24c7-5 15-5 22-1" stroke="currentColor" stroke-width="4" stroke-linecap="round"/>
                                    <path d="M48 56h24M48 70h24M48 84h16" stroke="currentColor" stroke-width="4" stroke-linecap="round"/>
                                    <path d="M82 50c7-7 14-10 23-9M86 62c8-3 15-3 22 0M83 76c7 2 13 6 18 12" stroke="currentColor" stroke-width="4" stroke-linecap="round"/>
                                    <circle cx="26" cy="39" r="4" fill="currentColor"/>
                                    <circle cx="29" cy="62" r="3" fill="currentColor"/>
                                    <circle cx="22" cy="83" r="4" fill="currentColor"/>
                                </svg>
                            @elseif($item['key'] === 'foam')
                                <svg class="media-main-icon" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M34 82c8-8 16-8 24 0s16 8 24 0 16-8 24 0" stroke="currentColor" stroke-width="4" stroke-linecap="round"/>
                                    <path d="M24 94c8-8 16-8 24 0s16 8 24 0 16-8 24 0" stroke="currentColor" stroke-width="4" stroke-linecap="round"/>
                                    <circle cx="37" cy="36" r="11" stroke="currentColor" stroke-width="4"/>
                                    <circle cx="63" cy="25" r="8" stroke="currentColor" stroke-width="4"/>
                                    <circle cx="78" cy="49" r="13" stroke="currentColor" stroke-width="4"/>
                                    <circle cx="52" cy="57" r="7" stroke="currentColor" stroke-width="4"/>
                                </svg>
                            @else
                                <svg class="media-main-icon" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M41 62h28a18 18 0 1 1 0 36H46a20 20 0 0 1-4-39.6" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M38 54c0-15 10-27 22-27s22 12 22 27" stroke="currentColor" stroke-width="4" stroke-linecap="round"/>
                                    <path d="M50 36V24M70 36V24M45 24h30" stroke="currentColor" stroke-width="4" stroke-linecap="round"/>
                                    <path d="M53 73h18M53 86h12" stroke="currentColor" stroke-width="4" stroke-linecap="round"/>
                                    <circle cx="88" cy="35" r="4" fill="currentColor"/>
                                    <circle cx="96" cy="52" r="3" fill="currentColor"/>
                                </svg>
                            @endif
                        </div>
                    </div>

                    <div class="media-copy">
                        <p class="media-kicker">Media Pemadam APAR</p>
                        <h3 class="media-title">{{ $item['title'] }}</h3>
                        <p class="media-desc">{{ $item['desc'] }}</p>
                        <div class="media-class-list" aria-label="Klasifikasi kebakaran untuk {{ $item['title'] }}">
                            @foreach($item['classes'] as $class)
                                @php
                                    $classMeta = match ($class) {
                                        'A' => [
                                            'label' => 'Kelas A',
                                            'desc' => 'Kayu / benda padat',
                                            'title' => 'Kelas A: kebakaran benda padat seperti kayu',
                                        ],
                                        'B' => [
                                            'label' => 'Kelas B',
                                            'desc' => 'Bensin / cairan mudah terbakar',
                                            'title' => 'Kelas B: kebakaran bensin atau cairan mudah terbakar',
                                        ],
                                        'C' => [
                                            'label' => 'Kelas C',
                                            'desc' => 'Listrik / elektronik',
                                            'title' => 'Kelas C: kebakaran akibat listrik atau peralatan elektronik',
                                        ],
                                        default => [
                                            'label' => 'Kelas D',
                                            'desc' => 'Logam',
                                            'title' => 'Kelas D: kebakaran logam tertentu',
                                        ],
                                    };
                                @endphp
                                <article class="media-class-card" title="{{ $classMeta['title'] }}" aria-label="{{ $classMeta['title'] }}">
                                    <div class="media-class-visual" aria-hidden="true">
                                        @if($class === 'A')
                                            <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M11 14h23a6 6 0 0 1 0 12H11a6 6 0 0 1 0-12Z" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M14 26h23a6 6 0 0 1 0 12H14a6 6 0 0 1 0-12Z" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                <circle cx="34" cy="20" r="2.4" stroke="currentColor" stroke-width="2.8"/>
                                                <circle cx="37" cy="32" r="2.4" stroke="currentColor" stroke-width="2.8"/>
                                                <path d="M15 14l8 12M26 14l8 12M18 26l8 12M29 26l8 12" stroke="currentColor" stroke-width="2.8" stroke-linecap="round"/>
                                            </svg>
                                        @elseif($class === 'B')
                                            <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M15 15h18l6 6v18a4 4 0 0 1-4 4H19a4 4 0 0 1-4-4V15Z" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M20 15V9h10v6M33 17h5l4 5v6" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M21 28h11M21 34h8" stroke="currentColor" stroke-width="2.8" stroke-linecap="round"/>
                                                <path d="M33 15v6h6" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        @elseif($class === 'C')
                                            <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M18 8v13M30 8v13" stroke="currentColor" stroke-width="2.8" stroke-linecap="round"/>
                                                <path d="M14 21h20v7a10 10 0 0 1-20 0v-7Z" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M24 38v4M24 42h8a6 6 0 0 0 6-6v-4" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M18 28h12" stroke="currentColor" stroke-width="2.8" stroke-linecap="round"/>
                                            </svg>
                                        @else
                                            <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M24 10v5M24 33v5M15 15l4 4M33 33l4 4M38 24h-5M15 24h-5M33 15l-4 4M15 33l4-4" stroke="currentColor" stroke-width="2.8" stroke-linecap="round"/>
                                                <circle cx="24" cy="24" r="9" stroke="currentColor" stroke-width="2.8"/>
                                                <circle cx="24" cy="24" r="3" stroke="currentColor" stroke-width="2.8"/>
                                            </svg>
                                        @endif
                                    </div>
                                    <strong class="media-class-label">{{ $classMeta['label'] }}</strong>
                                    <p class="media-class-desc">{{ $classMeta['desc'] }}</p>
                                </article>
                            @endforeach
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>

<section class="section-pad section-soft">
    <div class="container">
        <div class="section-head" data-reveal>
            <p class="section-tag">Keunggulan</p>
            <h2 class="section-title">Alasan Pelanggan Lebih Percaya Menggunakan Layanan Kami</h2>
            <p class="section-sub">Tampilan dibuat lebih rapi agar keunggulan usaha APAR terlihat jelas dan terasa lebih profesional bagi calon pelanggan.</p>
        </div>

        @php
            $feats = [
                ['title' => 'Administrasi Lebih Cepat', 'desc' => 'Admin lebih mudah menangani konsultasi, pemesanan, dan tindak lanjut layanan pelanggan.', 'icon' => 'fa-bolt', 'bg' => 'linear-gradient(135deg, #fff1f2 0%, #fee2e2 100%)', 'color' => '#b91c1c'],
                ['title' => 'Spesifikasi Produk Jelas', 'desc' => 'Produk APAR ditampilkan dengan jenis, ukuran, merek, dan harga yang lebih mudah dipahami.', 'icon' => 'fa-fire-extinguisher', 'bg' => 'linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%)', 'color' => '#c2410c'],
                ['title' => 'Harga Transparan', 'desc' => 'Harga produk dan layanan dibuat lebih jelas agar tidak membingungkan pelanggan.', 'icon' => 'fa-tags', 'bg' => 'linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%)', 'color' => '#1d4ed8'],
                ['title' => 'Layanan End-to-End', 'desc' => 'Pembelian, refill, service, inspeksi, dan monitoring APAR tercatat dalam satu sistem.', 'icon' => 'fa-layer-group', 'bg' => 'linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%)', 'color' => '#047857'],
                ['title' => 'Operasional Lebih Tertata', 'desc' => 'Stok, transaksi, dan riwayat layanan lebih mudah dipantau oleh admin maupun pelanggan.', 'icon' => 'fa-chart-line', 'bg' => 'linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%)', 'color' => '#334155'],
            ];
        @endphp

        <div class="feat-grid">
            @foreach($feats as $feat)
                <article class="feat-card" data-reveal>
                    <div class="feat-icon" style="background: {{ $feat['bg'] }}; color: {{ $feat['color'] }};">
                        <i class="fa-solid {{ $feat['icon'] }}"></i>
                    </div>
                    <h3 class="feat-title">{{ $feat['title'] }}</h3>
                    <p class="feat-desc">{{ $feat['desc'] }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>

<section class="section-pad bg-white">
    <div class="container">
        <div class="product-head" data-reveal>
            <div class="product-head-copy">
                <p class="section-tag">Produk</p>
                <h2 class="section-title">Produk APAR yang Tampil Lebih Menarik dan Mudah Dipahami</h2>
                <p class="section-sub">Jenis APAR, ukuran, merek, dan harga disusun lebih jelas agar pelanggan dapat melihat pilihan produk dengan cepat.</p>
            </div>
            <div class="product-head-actions">
                <a href="{{ route('produk.index') }}" class="btn-inline">
                    Lihat Semua Produk
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
                <a href="{{ $orderEntryUrl }}" class="btn-primary">
                    <i class="fa-solid fa-bag-shopping"></i>
                    Pesan Sekarang
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            @forelse($produks as $produk)
                <div data-reveal>
                    <x-product-card :produk="$produk" />
                </div>
            @empty
                <div class="landing-empty" data-reveal style="grid-column: 1 / -1;">
                    <i class="fa-solid fa-box-open"></i>
                    <strong>Belum ada produk APAR yang ditampilkan.</strong>
                    <span>Tambahkan data produk dari menu admin agar landing page menampilkan produk unggulan secara otomatis.</span>
                </div>
            @endforelse
        </div>
    </div>
</section>

<section class="section-pad section-soft">
    <div class="container">
        <div class="section-head" data-reveal>
            <p class="section-tag">Testimoni</p>
            <h2 class="section-title">Apa Kata Pelanggan</h2>
            <p class="section-sub">Ulasan pelanggan ditampilkan lebih fokus pada rating, nama, dan isi review agar tampil rapi dan tidak terasa kosong.</p>
        </div>

        <div class="{{ $testimoniLayout }}">
            @forelse($testimonis as $testimoni)
                <article class="testi-card" data-reveal>
                    <div class="testi-quote">
                        <i class="fa-solid fa-quote-right"></i>
                    </div>
                    <div class="testi-stars">
                        @for($i = 0; $i < $testimoni->rating; $i++)
                            <i class="fa-solid fa-star"></i>
                        @endfor
                        @for($i = $testimoni->rating; $i < 5; $i++)
                            <i class="fa-regular fa-star text-slate-300"></i>
                        @endfor
                    </div>
                    <p class="testi-text">{{ $testimoni->review }}</p>
                    <div class="testi-divider"></div>
                    <div class="testi-author">
                        <div class="testi-avatar">
                            {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($testimoni->pelanggan->nama ?? 'P', 0, 1)) }}
                        </div>
                        <div>
                            <p class="testi-name">{{ $testimoni->pelanggan->nama ?? 'Pelanggan' }}</p>
                            <p class="testi-role">Pelanggan PD. Anugrah Utama</p>
                        </div>
                    </div>
                </article>
            @empty
                <article class="testi-card" data-reveal style="max-width: 720px; margin: 0 auto;">
                    <div class="testi-quote">
                        <i class="fa-solid fa-quote-right"></i>
                    </div>
                    <div class="testi-stars">
                        @for($i = 0; $i < 5; $i++)
                            <i class="fa-solid fa-star"></i>
                        @endfor
                    </div>
                    <p class="testi-text">Testimoni pelanggan akan tampil di sini setelah transaksi selesai dan ulasan disetujui oleh admin. Tampilan dibuat lebih tenang agar tetap terlihat profesional walaupun data ulasan masih sedikit.</p>
                    <div class="testi-divider"></div>
                    <div class="testi-author">
                        <div class="testi-avatar">A</div>
                        <div>
                            <p class="testi-name">Ulasan Pelanggan</p>
                            <p class="testi-role">Akan muncul otomatis dari data sistem</p>
                        </div>
                    </div>
                </article>
            @endforelse
        </div>
    </div>
</section>

<section class="section-pad about-section">
    <div class="container">
        <div class="about-inner">
            <div data-reveal>
                <p class="about-tag">Tentang Kami</p>
                <h2 class="about-title">PD. Anugrah Utama membantu kebutuhan APAR dengan layanan yang lebih rapi dan meyakinkan</h2>
                <p class="about-desc">
                    Kami fokus pada penjualan APAR, refill, service, dan pemantauan riwayat unit APAR agar pelanggan mendapat proses yang lebih jelas sejak konsultasi awal sampai transaksi selesai.
                </p>
                <div class="about-points">
                    <div class="about-point">
                        <i class="fa-solid fa-check"></i>
                        <span>Pengalaman menangani kebutuhan APAR untuk rumah, toko, kantor, gudang, dan area operasional lainnya.</span>
                    </div>
                    <div class="about-point">
                        <i class="fa-solid fa-check"></i>
                        <span>Layanan APAR mencakup pembelian unit baru, refill, service, dan inspeksi berkala.</span>
                    </div>
                    <div class="about-point">
                        <i class="fa-solid fa-check"></i>
                        <span>Produk berkualitas dengan tampilan harga dan spesifikasi yang lebih jelas untuk pelanggan.</span>
                    </div>
                    <div class="about-point">
                        <i class="fa-solid fa-check"></i>
                        <span>Respon cepat melalui WhatsApp untuk konsultasi, pemesanan, maupun tindak lanjut layanan.</span>
                    </div>
                    <div class="about-point">
                        <i class="fa-solid fa-check"></i>
                        <span>Harga lebih transparan dengan pencatatan transaksi dan layanan yang lebih tertata.</span>
                    </div>
                </div>
                <div class="about-cta">
                    <a href="https://wa.me/{{ $waContact }}?text={{ urlencode('Halo, saya ingin konsultasi kebutuhan APAR.') }}" target="_blank" rel="noopener noreferrer" class="btn-whatsapp">
                        <i class="fa-brands fa-whatsapp"></i>
                        Hubungi WhatsApp
                    </a>
                    <a href="{{ $orderEntryUrl }}" class="btn-secondary">
                        <i class="fa-solid fa-cart-shopping"></i>
                        Pesan Sekarang
                    </a>
                </div>
            </div>

            @php
                $aboutCards = [
                    ['icon' => 'fa-fire-extinguisher', 'title' => 'Produk APAR', 'desc' => 'Pilihan produk APAR tampil lebih rapi dengan jenis, kapasitas, merek, dan harga yang jelas.', 'bg' => 'linear-gradient(135deg, rgba(248,113,113,0.18) 0%, rgba(254,242,242,0.22) 100%)', 'color' => '#fca5a5'],
                    ['icon' => 'fa-screwdriver-wrench', 'title' => 'Refill & Service', 'desc' => 'Layanan isi ulang dan service APAR dibuat lebih mudah dipahami dengan alur yang tertata.', 'bg' => 'linear-gradient(135deg, rgba(251,191,36,0.18) 0%, rgba(255,247,237,0.22) 100%)', 'color' => '#fbbf24'],
                    ['icon' => 'fa-shield-halved', 'title' => 'Inspeksi Unit', 'desc' => 'Riwayat unit APAR pelanggan dapat dipantau agar status dan masa berlaku lebih mudah dicek.', 'bg' => 'linear-gradient(135deg, rgba(96,165,250,0.16) 0%, rgba(239,246,255,0.20) 100%)', 'color' => '#93c5fd'],
                    ['icon' => 'fa-comments', 'title' => 'Konsultasi Cepat', 'desc' => 'Komunikasi dengan pelanggan lebih mudah melalui WhatsApp dan form pemesanan online.', 'bg' => 'linear-gradient(135deg, rgba(74,222,128,0.16) 0%, rgba(236,253,245,0.20) 100%)', 'color' => '#86efac'],
                ];
            @endphp

            <div class="about-grid">
                @foreach($aboutCards as $card)
                    <article class="about-card" data-reveal>
                        <div class="about-icon" style="background: {{ $card['bg'] }}; color: {{ $card['color'] }};">
                            <i class="fa-solid {{ $card['icon'] }}"></i>
                        </div>
                        <h3 class="about-card-title">{{ $card['title'] }}</h3>
                        <p class="about-card-desc">{{ $card['desc'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </div>
</section>

<section class="section-pad bg-white">
    <div class="container">
        <div class="section-head" data-reveal>
            <p class="section-tag">Lokasi</p>
            <h2 class="section-title">Temukan Kami dengan Lebih Mudah</h2>
            <p class="section-sub">Informasi alamat, WhatsApp, dan jam operasional ditata lebih jelas agar pelanggan mudah menghubungi dan datang ke lokasi.</p>
        </div>

        <div class="lokasi-grid">
            <div class="lokasi-info">
                <article class="lokasi-card" data-reveal>
                    <div class="lokasi-icon bg-red-50 text-red-600">
                        <i class="fa-solid fa-location-dot"></i>
                    </div>
                    <div>
                        <p class="lokasi-label">Alamat</p>
                        <p class="lokasi-text">Jl. Raya Bogor, Kota Bogor</p>
                        <p class="lokasi-sub">Jawa Barat, Indonesia. Silakan hubungi admin untuk arahan lokasi atau jadwal kunjungan.</p>
                    </div>
                </article>
                <article class="lokasi-card" data-reveal>
                    <div class="lokasi-icon bg-green-50 text-green-600">
                        <i class="fa-brands fa-whatsapp"></i>
                    </div>
                    <div>
                        <p class="lokasi-label">WhatsApp</p>
                        <a href="https://wa.me/{{ $waContact }}" target="_blank" rel="noopener noreferrer" class="lokasi-text">+62 851-2800-8030</a>
                        <p class="lokasi-sub">Gunakan WhatsApp untuk konsultasi, tanya stok, permintaan harga, dan penjadwalan layanan.</p>
                    </div>
                </article>
                <article class="lokasi-card" data-reveal>
                    <div class="lokasi-icon bg-blue-50 text-blue-600">
                        <i class="fa-solid fa-clock"></i>
                    </div>
                    <div>
                        <p class="lokasi-label">Jam Operasional</p>
                        <p class="lokasi-text">Senin - Sabtu</p>
                        <p class="lokasi-sub">08.00 - 17.00 WIB. Admin akan membalas pesan secepat mungkin pada jam operasional.</p>
                    </div>
                </article>
            </div>
            <div class="lokasi-map" data-reveal>
                <div id="location-map" style="width:100%; height:100%;"></div>
            </div>
        </div>
    </div>
</section>
@endsection
