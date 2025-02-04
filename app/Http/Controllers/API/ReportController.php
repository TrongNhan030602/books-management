<?php
namespace App\Http\Controllers\API;


use App\Models\Book;
use App\Models\User;
use App\Enums\RoleEnum;
use Illuminate\Http\Request;
use App\Models\BorrowTransaction;
use App\Exceptions\ErrorsException;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\BorrowTransactionService;
use DB;
class ReportController extends Controller
{
    protected $borrowTransactionService;

    public function __construct(BorrowTransactionService $borrowTransactionService)
    {
        $this->borrowTransactionService = $borrowTransactionService;
    }
    public function summary()
    {
        try {
            $data = [
                'total_books_init' => Book::sum('initial_quantity'),
                'total_books' => Book::sum('quantity'),
                'active_users' => User::where('role', 'reader')->count(),
                'borrowed_books' => BorrowTransaction::where('status', 'approved')->count(),
                'pending_requests' => BorrowTransaction::where('status', 'pending')->count(),
                'return_requests' => BorrowTransaction::where('status', 'returned')->count(),
            ];

            return response()->json(data: [
                'success' => true,
                'data' => $data,
            ], status: 200);
        } catch (ErrorsException $e) {
            return response()->json(data: [
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy dữ liệu: ' . $e->getMessage(),
            ], status: 500);
        }
    }
    public function getBorrowStats(Request $request)
    {
        $adminUserId = Auth::user()->id;
        $userId = $request->input('user_id');

        if ($userId) {
            // Admin chỉ được phép xem thống kê của người dùng khác
            if (Auth::user()->role !== RoleEnum::Admin) {
                return response()->json([
                    'error' => 'Không đủ quyền truy cập'
                ], 403);
            }
        } else {
            // Nếu không truyền user_id, thống kê cho tất cả người dùng
            $userId = null;
        }

        try {
            $stats = $this->borrowTransactionService->getBorrowStats($userId); // Truyền userId vào service
            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Thống kê giao dịch mượn sách thành công'
            ], 200);
        } catch (ErrorsException $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], $e->getCode());
        }
    }


    public function index(Request $request)
    {
        // Lọc theo khoảng thời gian nếu có
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Chọn nhóm dữ liệu theo thời gian (ngày, tháng, năm)
        $groupBy = $request->input('group_by', 'day'); // Mặc định là nhóm theo 'day'

        $query = BorrowTransaction::query();

        // Lọc theo khoảng thời gian nếu có
        if ($startDate && $endDate) {
            $query->whereBetween('borrow_date', [$startDate, $endDate]);
        }

        // Định dạng nhóm theo thời gian
        $dateFormat = $groupBy == 'month' ? '%Y-%m' : ($groupBy == 'year' ? '%Y' : '%Y-%m-%d');

        // Nhóm theo thời gian đã chọn
        $stats = $query->select(
            DB::raw("DATE_FORMAT(borrow_date, '{$dateFormat}') as period"),
            DB::raw('count(*) as total_transactions'),
            DB::raw('sum(case when status = "pending" then 1 else 0 end) as total_pending'),
            DB::raw('sum(case when status = "approved" then 1 else 0 end) as total_approved'),
            DB::raw('sum(case when status = "returned" then 1 else 0 end) as total_returned'),
            DB::raw('sum(case when status = "completed" then 1 else 0 end) as total_completed'),
            DB::raw('sum(case when status = "cancelled" then 1 else 0 end) as total_cancelled')
        )
            ->groupBy(DB::raw("DATE_FORMAT(borrow_date, '{$dateFormat}')"))
            ->orderBy('period', 'asc')
            ->get();

        // Chuẩn bị dữ liệu cho biểu đồ
        $labels = [];
        $totalTransactions = [];
        $totalPending = [];
        $totalApproved = [];
        $totalReturned = [];
        $totalCompleted = [];
        $totalCancelled = [];

        foreach ($stats as $stat) {
            $labels[] = $stat->period;
            // Đảm bảo các giá trị là số nguyên
            $totalTransactions[] = (int) $stat->total_transactions;
            $totalPending[] = (int) $stat->total_pending;
            $totalApproved[] = (int) $stat->total_approved;
            $totalReturned[] = (int) $stat->total_returned;
            $totalCompleted[] = (int) $stat->total_completed;
            $totalCancelled[] = (int) $stat->total_cancelled;
        }

        // Trả về dữ liệu dưới dạng thích hợp cho việc vẽ biểu đồ
        return response()->json([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Total Transactions',
                    'data' => $totalTransactions,
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                    'borderWidth' => 1
                ],
                [
                    'label' => 'Pending',
                    'data' => $totalPending,
                    'backgroundColor' => 'rgba(255, 159, 64, 0.2)',
                    'borderColor' => 'rgba(255, 159, 64, 1)',
                    'borderWidth' => 1
                ],
                [
                    'label' => 'Approved',
                    'data' => $totalApproved,
                    'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'borderWidth' => 1
                ],
                [
                    'label' => 'Returned',
                    'data' => $totalReturned,
                    'backgroundColor' => 'rgba(153, 102, 255, 0.2)',
                    'borderColor' => 'rgba(153, 102, 255, 1)',
                    'borderWidth' => 1
                ],
                [
                    'label' => 'Completed',
                    'data' => $totalCompleted,
                    'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                    'borderColor' => 'rgba(255, 99, 132, 1)',
                    'borderWidth' => 1
                ],
                [
                    'label' => 'Cancelled',
                    'data' => $totalCancelled,
                    'backgroundColor' => 'rgba(255, 159, 64, 0.2)',
                    'borderColor' => 'rgba(255, 159, 64, 1)',
                    'borderWidth' => 1
                ]
            ]
        ]);
    }


}