<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            color: #333;
            line-height: 1.6;
        }
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 30px;
            border: 1px solid #e0e0e0;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            border-bottom: 2px solid #1a237e;
            padding-bottom: 20px;
        }
        .logo {
            max-width: 150px;
        }
        .company-info {
            text-align: right;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #1a237e;
            margin-bottom: 5px;
        }
        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            color: #1a237e;
            text-align: center;
            margin-bottom: 30px;
        }
        .details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .client-info, .invoice-info {
            width: 48%;
        }
        .section-title {
            font-size: 18px;
            font-weight: bold;
            color: #1a237e;
            margin-bottom: 10px;
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th {
            background-color: #1a237e;
            color: white;
            padding: 10px;
            text-align: left;
        }
        td {
            padding: 10px;
            border-bottom: 1px solid #e0e0e0;
        }
        .total {
            font-weight: bold;
            font-size: 18px;
            text-align: right;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #777;
            border-top: 1px solid #e0e0e0;
            padding-top: 20px;
        }
        .highlight {
            color: #d4af37; /* Gold */
        }
    </style>
</head>
<body>
    <div class="invoice-container">
            <div class="company-info">
                <div class="company-name">Yudistira Gadget</div>
                <div>Jl. Raya Puncak No 2 </div>
                <div>Kabupaten Bogor, 321092</div>
                <div>Email: <span class="highlight">gadgetkuy@sangkuriang.id</span></div>
                <div>Telp: <span class="highlight">(021) 1234-5678</span></div>
            </div>
        </div>

        <div class="invoice-title">INVOICE</div>

        <div class="details">
            <div class="invoice-info">
                <div class="section-title">Detail Invoice:</div>
                <div>No. Invoice: <strong>{{ $sales->invoice_number }}</strong></div>
                <div>Tanggal: {{ $sales->created_at->format('d F Y H:i') }}</div>
                <div>Poin Didapat: {{ $points }}</div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Jumlah</th>
                    <th>Harga Satuan</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cartData as $item)
                <tr>
                    <td>{{ $item['nama'] }}</td>
                    <td>{{ $item['jumlah'] }}</td>
                    <td>Rp {{ number_format($item['subtotal'] / $item['jumlah'], 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="total">
            <div>Subtotal: Rp {{ number_format($subtotal, 0, ',', '.') }}</div>
            <div>Total Bayar: Rp {{ number_format($totalPaid, 0, ',', '.') }}</div>
            <div>Kembalian: <span class="highlight">Rp {{ number_format($kembalian, 0, ',', '.') }}</span></div>
        </div>

        <div class="footer">
            <div>Terima kasih telah berbelanja di toko kami!</div>
            <div>Barang yang sudah dibeli tidak dapat dikembalikan</div>
            <div>Jika ada pertanyaan, hubungi (021) 1234-5678</div>
        </div>
    </div>
</body>
</html>