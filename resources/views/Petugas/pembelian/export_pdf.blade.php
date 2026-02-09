<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #333;
        }

        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 25px;
            border: 1px solid #e0e0e0;
        }

        /* ===== HEADER ===== */
        .company-info {
            text-align: right;
            margin-bottom: 20px;
        }

        .company-name {
            font-size: 20px;
            font-weight: bold;
            color: #1a237e;
        }

        .invoice-title {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            color: #1a237e;
            margin: 20px 0;
        }

        /* ===== DETAIL ===== */
        .details {
            margin-bottom: 25px;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #1a237e;
            border-bottom: 1px solid #ddd;
            margin-bottom: 8px;
            padding-bottom: 4px;
        }

        /* ===== TABLE ===== */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th {
            background: #1a237e;
            color: #fff;
            padding: 8px;
            text-align: left;
            font-size: 11px;
        }

        td {
            padding: 8px;
            border-bottom: 1px solid #e0e0e0;
        }

        /* ===== SUMMARY (KANAN BAWAH) ===== */
        .summary {
            width: 42%;
            margin-left: auto;
            font-size: 11px;
        }

        .summary-row {
            display: table;
            width: 100%;
            padding: 4px 0;
        }

        .summary-row span {
            display: table-cell;
        }

        .summary-row span:first-child {
            color: #6b7280;
        }

        .summary-row span:last-child {
            text-align: right;
            width: 140px;
        }

        .summary-row.total {
            font-weight: bold;
            color: #111827;
            font-size: 12px;
        }

        .summary-row.border-top {
            border-top: 1px solid #9ca3af;
            padding-top: 6px;
            margin-top: 6px;
        }

        .summary-row.grand {
            font-weight: bold;
            color: #1a237e;
            font-size: 13px;
        }

        /* ===== FOOTER ===== */
        .footer {
            margin-top: 35px;
            text-align: center;
            font-size: 10px;
            color: #777;
            border-top: 1px solid #e0e0e0;
            padding-top: 15px;
        }
    </style>
</head>
<body>

<div class="invoice-container">
    <div class="company-info">
        <div class="company-name">Yudistira Gadget</div>
        <div>Jl. Raya Puncak No 2</div>
        <div>Kabupaten Bogor</div>
        <div>Email: gadgetkuy@sangkuriang.id</div>
        <div>Telp: (021) 1234-5678</div>
    </div>

    <div class="invoice-title">INVOICE</div>
        <div class="details">
            <div class="section-title">Detail Invoice</div>
            <div>No Invoice : <strong>{{ $sales->invoice_number }}</strong></div>
            <div>Tanggal : {{ $sales->created_at->format('d F Y H:i') }}</div>
            <div>Member : {{ $member->name ?? 'Bukan Member' }}</div>
            <div>Poin Digunakan : {{ number_format($points, 0, ',', '.') }}</div>
        </div>

    <!-- TABLE -->
    <table>
        <thead>
            <tr>
                <th>Produk</th>
                <th width="80">Jumlah</th>
                <th width="120">Harga</th>
                <th width="140">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cartData as $item)
            <tr>
                <td>{{ $item['nama'] }}</td>
                <td>{{ $item['jumlah'] }}</td>
                <td>Rp {{ number_format($item['subtotal'] / max($item['jumlah'],1), 0, ',', '.') }}</td>
                <td>Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- SUMMARY -->
    <div class="summary">
        <div class="summary-row">
            <span>SUBTOTAL</span>
            <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
        </div>

        @if($points > 0)
        <div class="summary-row">
            <span>DISKON POIN</span>
            <span>- Rp {{ number_format($points, 0, ',', '.') }}</span>
        </div>
        @endif

        <div class="summary-row total">
            <span>TOTAL</span>
            <span>Rp {{ number_format($totalAfterDiscount, 0, ',', '.') }}</span>
        </div>

        <div class="summary-row">
            <span>PEMBAYARAN</span>
            <span>Rp {{ number_format($totalPaid, 0, ',', '.') }}</span>
        </div>

        <div class="summary-row border-top grand">
            <span>KEMBALIAN</span>
            <span>Rp {{ number_format($kembalian, 0, ',', '.') }}</span>
        </div>
    </div>

    <!-- FOOTER -->
    <div class="footer">
        <div>Terima kasih telah berbelanja</div>
        <div>Barang yang sudah dibeli tidak dapat dikembalikan</div>
    </div>
</div>

</body>
</html>
