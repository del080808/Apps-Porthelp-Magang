import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../core/constants/api_constants.dart';

class TicketService {
  static Future<String?> _getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('token');
  }

  static Future<Map<String, String>> _headers() async {
    final token = await _getToken();
    return {
      'Content-Type': 'application/json',
      'Authorization': 'Bearer $token',
    };
  }

  // Ambil semua tiket
  static Future<List<dynamic>> getTickets() async {
    final response = await http.get(
      Uri.parse('${ApiConstants.baseUrl}/tickets'),
      headers: await _headers(),
    );
    return jsonDecode(response.body);
  }

  // Buat tiket baru
  static Future<Map<String, dynamic>> createTicket({
    required String title,
    required String description,
    required String priority,
    required String category,
  }) async {
    final response = await http.post(
      Uri.parse('${ApiConstants.baseUrl}/tickets'),
      headers: await _headers(),
      body: jsonEncode({
        'title': title,
        'description': description,
        'priority': priority,
        'category': category,
      }),
    );
    return {'status': response.statusCode, 'data': jsonDecode(response.body)};
  }

  // Update status tiket
  static Future<Map<String, dynamic>> updateTicket(
      int id, String status, {String? resolutionNotes}) async {
    final response = await http.put(
      Uri.parse('${ApiConstants.baseUrl}/tickets/$id'),
      headers: await _headers(),
      body: jsonEncode({
        'status': status,
        'resolution_notes': resolutionNotes,
      }),
    );
    return {'status': response.statusCode, 'data': jsonDecode(response.body)};
  }

  // Assign tiket ke teknisi
  static Future<Map<String, dynamic>> assignTicket(
      int ticketId, int teknisiId) async {
    final response = await http.post(
      Uri.parse('${ApiConstants.baseUrl}/tickets/$ticketId/assign'),
      headers: await _headers(),
      body: jsonEncode({'teknisi_id': teknisiId}),
    );
    return {'status': response.statusCode, 'data': jsonDecode(response.body)};
  }
}