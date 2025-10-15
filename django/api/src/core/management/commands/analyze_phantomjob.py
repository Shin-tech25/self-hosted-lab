from django.core.management.base import BaseCommand, CommandError
from core.utils.phantom_analysis import analyze_phantom_job

class Command(BaseCommand):
    help = "Analyze PhantomJob by magic: derive path_analysis & r_analysis from ClosedPosition."

    def add_arguments(self, parser):
        parser.add_argument("magic", type=int, help="PhantomJob.magic")
        parser.add_argument("--apply", action="store_true", help="Save results to PhantomJob (disable dry-run)")

    def handle(self, *args, **options):
        magic = options["magic"]
        dry_run = not options["apply"]
        try:
            res = analyze_phantom_job(magic, dry_run=dry_run)
        except Exception as e:
            raise CommandError(str(e))

        if res.get("saved"):
            self.stdout.write(self.style.SUCCESS(
                f"Saved analysis to PhantomJob#{res['job_id']} (magic={magic})."
            ))
        else:
            self.stdout.write(self.style.WARNING(
                f"DRY-RUN complete for PhantomJob#{res['job_id']} (magic={magic})."
            ))
