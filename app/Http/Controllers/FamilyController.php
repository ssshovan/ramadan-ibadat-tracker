<?php

namespace App\Http\Controllers;

use App\Models\Family;
use App\Models\FamilyMember;
use App\Services\FamilyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * Family Controller
 * 
 * Handles family-related operations.
 * Create, join, manage families and view family progress.
 */
class FamilyController extends Controller
{
    protected $familyService;

    /**
     * Constructor with dependency injection.
     */
    public function __construct(FamilyService $familyService)
    {
        $this->familyService = $familyService;
    }

    /**
     * Display family dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();

        // Get user's family memberships
        $memberships = $user->familyMemberships()
                            ->with('family')
                            ->where('is_active', true)
                            ->get();

        // If user has no family, show join/create page
        if ($memberships->isEmpty()) {
            return view('family.welcome');
        }

        // Get primary family details
        $primaryFamily = $memberships->first()->family;
        $familyDetails = $this->familyService->getFamilyDetails($primaryFamily->id);

        // Check if user is parent
        $isParent = $primaryFamily->hasParent($user->id);

        return view('family.index', compact(
            'memberships',
            'primaryFamily',
            'familyDetails',
            'isParent'
        ));
    }

    /**
     * Show create family form.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('family.create');
    }

    /**
     * Store a new family.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Validate request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        // Create family
        $family = Family::create([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'created_by' => $user->id,
            'family_streak' => 0,
        ]);

        // Add creator as parent
        $family->addMember($user->id, 'parent');

        return redirect()->route('family.index')
                         ->with('success', 'Family created successfully! Family Code: ' . $family->family_code);
    }

    /**
     * Show join family form.
     *
     * @return \Illuminate\View\View
     */
    public function showJoinForm()
    {
        return view('family.join');
    }

    /**
     * Join a family using code.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function join(Request $request)
    {
        $user = Auth::user();

        // Validate request
        $validator = Validator::make($request->all(), [
            'family_code' => 'required|string|size:8',
            'role' => 'required|in:parent,child',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        $familyCode = strtoupper($request->input('family_code'));

        // Find family
        $family = Family::byCode($familyCode)->first();

        if (!$family) {
            return redirect()->back()
                           ->with('error', 'Invalid family code. Please check and try again.');
        }

        // Check if already a member
        if ($family->hasMember($user->id)) {
            return redirect()->route('family.index')
                           ->with('info', 'You are already a member of this family!');
        }

        // Add member
        $family->addMember($user->id, $request->input('role'));

        return redirect()->route('family.index')
                         ->with('success', 'Welcome to ' . $family->name . '! MashaAllah!');
    }

    /**
     * Show family details.
     *
     * @param int $familyId
     * @return \Illuminate\View\View
     */
    public function show(int $familyId)
    {
        $user = Auth::user();
        $family = Family::findOrFail($familyId);

        // Check if user is member
        if (!$family->hasMember($user->id)) {
            abort(403, 'You are not a member of this family.');
        }

        $familyDetails = $this->familyService->getFamilyDetails($familyId);
        $isParent = $family->hasParent($user->id);

        return view('family.show', compact('family', 'familyDetails', 'isParent'));
    }

    /**
     * Show parent dashboard.
     *
     * @param int $familyId
     * @return \Illuminate\View\View
     */
    public function parentDashboard(int $familyId)
    {
        $user = Auth::user();
        $family = Family::findOrFail($familyId);

        // Check if user is parent
        if (!$family->hasParent($user->id)) {
            abort(403, 'Only parents can access this dashboard.');
        }

        $children = $family->children()->get();
        $childrenProgress = [];

        foreach ($children as $child) {
            $childrenProgress[] = [
                'user' => $child,
                'today_progress' => $this->familyService->getMemberTodayProgress($child->id),
                'weekly_progress' => $this->familyService->getMemberWeeklyProgress($child->id),
                'streaks' => $child->streaks,
            ];
        }

        return view('family.parent-dashboard', compact('family', 'childrenProgress'));
    }

    /**
     * Leave a family.
     *
     * @param int $familyId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function leave(int $familyId)
    {
        $user = Auth::user();
        $family = Family::findOrFail($familyId);

        // Cannot leave if creator
        if ($family->created_by === $user->id) {
            return redirect()->back()
                           ->with('error', 'As the creator, you cannot leave the family. Transfer ownership first or delete the family.');
        }

        $family->removeMember($user->id);

        return redirect()->route('family.index')
                         ->with('success', 'You have left the family.');
    }

    /**
     * Remove a member (parent only).
     *
     * @param Request $request
     * @param int $familyId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function removeMember(Request $request, int $familyId)
    {
        $user = Auth::user();
        $family = Family::findOrFail($familyId);

        // Check if user is parent
        if (!$family->hasParent($user->id)) {
            abort(403, 'Only parents can remove members.');
        }

        $memberId = $request->input('member_id');
        $memberUser = FamilyMember::where('family_id', $familyId)
                                   ->where('user_id', $memberId)
                                   ->first();

        if (!$memberUser) {
            return redirect()->back()->with('error', 'Member not found.');
        }

        // Cannot remove creator
        if ($family->created_by === $memberId) {
            return redirect()->back()->with('error', 'Cannot remove the family creator.');
        }

        $family->removeMember($memberId);

        return redirect()->back()->with('success', 'Member removed successfully.');
    }

    /**
     * Update member role (parent only).
     *
     * @param Request $request
     * @param int $familyId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateRole(Request $request, int $familyId)
    {
        $user = Auth::user();
        $family = Family::findOrFail($familyId);

        // Check if user is parent
        if (!$family->hasParent($user->id)) {
            abort(403, 'Only parents can update roles.');
        }

        $validator = Validator::make($request->all(), [
            'member_id' => 'required|integer|exists:family_members,user_id',
            'role' => 'required|in:parent,child',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        $member = FamilyMember::where('family_id', $familyId)
                              ->where('user_id', $request->input('member_id'))
                              ->first();

        if ($member) {
            $member->role = $request->input('role');
            $member->save();
        }

        return redirect()->back()->with('success', 'Role updated successfully.');
    }

    /**
     * Get family progress (AJAX).
     *
     * @param int $familyId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFamilyProgress(int $familyId)
    {
        $user = Auth::user();
        $family = Family::findOrFail($familyId);

        // Check if user is member
        if (!$family->hasMember($user->id)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $progress = $this->familyService->getFamilyDetails($familyId);

        return response()->json($progress);
    }

    /**
     * Delete family (creator only).
     *
     * @param int $familyId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(int $familyId)
    {
        $user = Auth::user();
        $family = Family::findOrFail($familyId);

        // Only creator can delete
        if ($family->created_by !== $user->id) {
            abort(403, 'Only the creator can delete the family.');
        }

        $family->delete();

        return redirect()->route('family.index')
                         ->with('success', 'Family deleted successfully.');
    }
}
