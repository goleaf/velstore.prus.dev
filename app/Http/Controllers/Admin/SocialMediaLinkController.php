<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\SocialMediaLink;
use App\Services\Admin\SocialMediaLinkService;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class SocialMediaLinkController extends Controller
{
    protected $socialMediaLinkService;

    public function __construct(SocialMediaLinkService $socialMediaLinkService)
    {
        $this->socialMediaLinkService = $socialMediaLinkService;
    }

    public function index()
    {
        $socialMediaLinks = $this->socialMediaLinkService->getAllSocialMediaLinks();

        return view('admin.social-media-links.index', compact('socialMediaLinks'));
    }

    public function getData(Request $request)
    {
        $socialMediaLinks = SocialMediaLink::query();

        return DataTables::of($socialMediaLinks)
            ->addColumn('action', function ($socialMediaLink) {
                $editRoute = route('admin.social-media-links.edit', $socialMediaLink->id);

                return '<div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-primary btn-edit-social-link" data-url="'.$editRoute.'">
                                <i class="bi bi-pencil-fill"></i>
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-delete-social-link" data-id="'.$socialMediaLink->id.'">
                                <i class="bi bi-trash-fill"></i>
                            </button>
                        </div>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    // Other controller methods...

    public function create()
    {
        $languages = Language::where('active', 1)->get();

        return view('admin.social-media-links.create', compact('languages'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:facebook,instagram,tiktok,youtube,x',
            'platform' => 'required|string|max:255',
            'link' => 'required|url',
            'languages.*.name' => 'required|string|max:255',
        ]);

        $this->socialMediaLinkService->createSocialMediaLink($request->all());

        return redirect()->route('admin.social-media-links.index')->with('success', __('cms.social_media_links.created'));
    }

    public function edit($id)
    {
        $socialMediaLink = $this->socialMediaLinkService->getAllSocialMediaLinks()->find($id);
        $languages = Language::where('active', 1)->get();
        $translations = $socialMediaLink->translations->keyBy('language_code');

        return view('admin.social-media-links.edit', compact('socialMediaLink', 'languages', 'translations'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'type' => 'required|in:facebook,instagram,tiktok,youtube,x',
            'platform' => 'required|string|max:255',
            'link' => 'required|url',
            'languages.*.name' => 'required|string|max:255',
        ]);

        $this->socialMediaLinkService->updateSocialMediaLink($id, $request->all());

        return redirect()->route('admin.social-media-links.index')->with('success', __('cms.social_media_links.updated'));
    }

    public function destroy($id)
    {
        try {
            $socialMediaLink = SocialMediaLink::findOrFail($id);
            $socialMediaLink->delete();

            return response()->json([
                'success' => true,
                'message' => __('cms.social_media_links.deleted'),
            ]);
        } catch (\Exception $e) {
            \Log::error("Error deleting social media link with ID {$id}: ".$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the social media link.',
            ]);
        }
    }
}
