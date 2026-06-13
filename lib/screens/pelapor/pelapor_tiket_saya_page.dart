import 'package:flutter/material.dart';
import '../../core/theme/app_theme.dart';
import '../../models/ticket_model.dart';
import '../../services/ticket_service.dart'; // ✅ ganti DataService
import 'pelapor_detail_tiket_page.dart';
import 'widgets/pelapor_ticket_card.dart';

class PelaporTiketSayaPage extends StatefulWidget {
  const PelaporTiketSayaPage({super.key});

  @override
  State<PelaporTiketSayaPage> createState() => _PelaporTiketSayaPageState();
}

class _PelaporTiketSayaPageState extends State<PelaporTiketSayaPage> {
  String _searchQuery = '';
  String _filterStatus = 'Semua';
  String _sortBy = 'Terbaru';
  final List<String> _sortOptions = ['Terbaru', 'Terlama', 'Prioritas Tertinggi'];
  final TextEditingController _searchController = TextEditingController();

  // ✅ Ganti dari DataService ke API
  List<Ticket> _tickets = [];
  bool _isLoading = true;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _loadTickets();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  // ✅ Fungsi ambil tiket dari API
  Future<void> _loadTickets() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final data = await TicketService.getTickets();
      setState(() {
        _tickets = data.map((json) => Ticket.fromJson(json)).toList();
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _errorMessage = 'Gagal memuat tiket: ${e.toString()}';
        _isLoading = false;
      });
    }
  }

  List<Ticket> get _filteredTickets {
    final filtered = _tickets.where((ticket) {
      final matchesSearch =
          ticket.title.toLowerCase().contains(_searchQuery.toLowerCase()) ||
          ticket.description.toLowerCase().contains(_searchQuery.toLowerCase()) ||
          ticket.id.toLowerCase().contains(_searchQuery.toLowerCase());

      final matchesStatus =
          _filterStatus == 'Semua' ||
          ticket.status.toLowerCase().contains(_filterStatus.toLowerCase());

      return matchesSearch && matchesStatus;
    }).toList();

    switch (_sortBy) {
      case 'Terlama':
        filtered.sort((a, b) => a.date.compareTo(b.date));
        break;
      case 'Prioritas Tertinggi':
        const priorityOrder = {'Critical': 0, 'High': 1, 'Medium': 2, 'Low': 3};
        filtered.sort(
          (a, b) => (priorityOrder[a.priority] ?? 9)
              .compareTo(priorityOrder[b.priority] ?? 9),
        );
        break;
      case 'Terbaru':
      default:
        filtered.sort((a, b) => b.date.compareTo(a.date));
        break;
    }

    return filtered;
  }

  @override
  Widget build(BuildContext context) {
    final statusFilters = ['Semua', 'Terbuka', 'Dikerjakan', 'Selesai'];
    final filteredList = _filteredTickets;

    return Scaffold(
      backgroundColor: AppPalette.background,
      appBar: AppBar(
        automaticallyImplyLeading: false,
        title: const Text(
          'Tiket Saya',
          style: TextStyle(fontWeight: FontWeight.normal),
        ),
        centerTitle: true,
        backgroundColor: AppPalette.primary,
        foregroundColor: Colors.white,
        elevation: 0,
        // ✅ Tambah tombol refresh
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadTickets,
            tooltip: 'Refresh',
          ),
        ],
      ),
      body: _isLoading
          // ✅ Loading state
          ? const Center(child: CircularProgressIndicator())
          // ✅ Error state
          : _errorMessage != null
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Icon(Icons.error_outline, size: 64, color: Colors.red),
                      const SizedBox(height: 16),
                      Text(
                        _errorMessage!,
                        textAlign: TextAlign.center,
                        style: const TextStyle(color: Colors.red),
                      ),
                      const SizedBox(height: 16),
                      ElevatedButton(
                        onPressed: _loadTickets,
                        child: const Text('Coba Lagi'),
                      ),
                    ],
                  ),
                )
              : Column(
                  children: [
                    Container(
                      color: AppPalette.surface,
                      padding: const EdgeInsets.fromLTRB(16, 12, 16, 0),
                      child: Column(
                        children: [
                          TextField(
                            controller: _searchController,
                            onChanged: (value) =>
                                setState(() => _searchQuery = value),
                            decoration: InputDecoration(
                              hintText: 'Cari ID tiket atau judul masalah...',
                              hintStyle: TextStyle(
                                fontSize: 13,
                                color: AppPalette.textSecondary.withOpacity(0.7),
                              ),
                              prefixIcon: const Icon(
                                Icons.search,
                                color: AppPalette.textSecondary,
                              ),
                              suffixIcon: _searchQuery.isNotEmpty
                                  ? IconButton(
                                      icon: const Icon(Icons.clear, size: 18),
                                      color: AppPalette.textSecondary,
                                      onPressed: () {
                                        _searchController.clear();
                                        setState(() => _searchQuery = '');
                                      },
                                    )
                                  : null,
                              contentPadding:
                                  const EdgeInsets.symmetric(vertical: 0),
                              border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(12),
                                borderSide: BorderSide.none,
                              ),
                            ),
                          ),
                          const SizedBox(height: 12),
                          SizedBox(
                            height: 36,
                            child: ListView.separated(
                              scrollDirection: Axis.horizontal,
                              itemCount: statusFilters.length,
                              separatorBuilder: (_, __) =>
                                  const SizedBox(width: 8),
                              itemBuilder: (context, index) {
                                final filter = statusFilters[index];
                                final isSelected = _filterStatus == filter;
                                return GestureDetector(
                                  onTap: () =>
                                      setState(() => _filterStatus = filter),
                                  child: AnimatedContainer(
                                    duration: const Duration(milliseconds: 200),
                                    padding: const EdgeInsets.symmetric(
                                      horizontal: 18,
                                      vertical: 8,
                                    ),
                                    decoration: BoxDecoration(
                                      color: isSelected
                                          ? AppPalette.primary
                                          : AppPalette.mutedSurface,
                                      borderRadius: BorderRadius.circular(20),
                                    ),
                                    child: Text(
                                      filter,
                                      style: TextStyle(
                                        fontSize: 13,
                                        fontWeight: FontWeight.w600,
                                        color: isSelected
                                            ? Colors.white
                                            : AppPalette.textSecondary,
                                      ),
                                    ),
                                  ),
                                );
                              },
                            ),
                          ),
                          const SizedBox(height: 10),
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Text(
                                '${filteredList.length} tiket ditemukan',
                                style: TextStyle(
                                  fontSize: 12,
                                  color: AppPalette.textSecondary,
                                ),
                              ),
                              Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Icon(Icons.sort_rounded,
                                      size: 16,
                                      color: AppPalette.textSecondary),
                                  const SizedBox(width: 4),
                                  DropdownButton<String>(
                                    value: _sortBy,
                                    underline: const SizedBox(),
                                    isDense: true,
                                    icon: Icon(Icons.arrow_drop_down,
                                        size: 18, color: AppPalette.primary),
                                    style: TextStyle(
                                      fontSize: 12,
                                      color: AppPalette.primary,
                                      fontWeight: FontWeight.w600,
                                    ),
                                    items: _sortOptions
                                        .map((s) => DropdownMenuItem(
                                            value: s, child: Text(s)))
                                        .toList(),
                                    onChanged: (v) {
                                      if (v != null)
                                        setState(() => _sortBy = v);
                                    },
                                  ),
                                ],
                              ),
                            ],
                          ),
                          const SizedBox(height: 8),
                        ],
                      ),
                    ),
                    Expanded(
                      child: filteredList.isEmpty
                          ? Center(
                              child: Column(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  Icon(Icons.confirmation_number_outlined,
                                      size: 64, color: AppPalette.border),
                                  const SizedBox(height: 16),
                                  Text(
                                    'Tidak ada tiket',
                                    style: TextStyle(
                                      fontSize: 16,
                                      color: AppPalette.textSecondary,
                                      fontWeight: FontWeight.w600,
                                    ),
                                  ),
                                  const SizedBox(height: 8),
                                  Text(
                                    'Coba ubah filter atau buat tiket baru',
                                    style: TextStyle(
                                      fontSize: 13,
                                      color: AppPalette.textSecondary
                                          .withOpacity(0.7),
                                    ),
                                  ),
                                ],
                              ),
                            )
                          : RefreshIndicator( // ✅ Pull to refresh
                              onRefresh: _loadTickets,
                              child: ListView.builder(
                                padding: const EdgeInsets.fromLTRB(
                                    16, 12, 16, 16),
                                itemCount: filteredList.length,
                                itemBuilder: (context, index) {
                                  final ticket = filteredList[index];
                                  return PelaporTicketCard(
                                    ticket: ticket,
                                    onTap: () {
                                      Navigator.push(
                                        context,
                                        MaterialPageRoute(
                                          builder: (_) =>
                                              PelaporDetailTiketPage(
                                                  ticket: ticket),
                                        ),
                                      );
                                    },
                                  );
                                },
                              ),
                            ),
                    ),
                  ],
                ),
    );
  }
}